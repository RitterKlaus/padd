#!/usr/bin/env bash
#
# Deploy auf den Shared Hoster:
#   1. Prüfen, ob der lokale Stand committet und gepusht ist
#   2. Assets lokal bauen (npm run build)
#   3. Server: Wartungsmodus an, Code per git aktualisieren
#   4. public/build per rsync hochladen
#   5. Server: composer install, migrate, optimize, Wartungsmodus aus
#
# Konfiguration: .deploy.env (Vorlage: .deploy.env.example)
# Aufruf:        ./deploy.sh [--dry-run]

set -Eeuo pipefail

cd "$(dirname "$0")"

DRY_RUN=false
for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=true ;;
        -h|--help)
            sed -n '2,11p' "$0" | sed 's/^# \{0,1\}//'
            exit 0
            ;;
        *)
            echo "Unbekannte Option: $arg" >&2
            exit 1
            ;;
    esac
done

info() { printf '\n\033[1;34m==> %s\033[0m\n' "$*"; }
fail() { printf '\n\033[1;31mFehler: %s\033[0m\n' "$*" >&2; exit 1; }

if [[ ! -f .deploy.env ]]; then
    fail ".deploy.env fehlt. Kopiere .deploy.env.example nach .deploy.env und passe die Werte an."
fi

# shellcheck source=/dev/null
source .deploy.env

: "${DEPLOY_HOST:?DEPLOY_HOST ist in .deploy.env nicht gesetzt}"
: "${DEPLOY_PATH:?DEPLOY_PATH ist in .deploy.env nicht gesetzt}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"
DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
REMOTE_PHP="${REMOTE_PHP:-php}"
REMOTE_COMPOSER="${REMOTE_COMPOSER:-php composer.phar}"

SSH=(ssh -p "$DEPLOY_PORT" "$DEPLOY_HOST")

# Führt ein Bash-Skript (von stdin) im Projektordner auf dem Server aus.
remote() {
    # ssh fügt alle Argumente zu einer Zeile zusammen, die die Remote-Shell neu
    # aufteilt. Deshalb jedes Argument quoten, sonst wird z. B.
    # "php composer.phar" zu zwei Argumenten.
    local remote_args
    remote_args="$(printf '%q ' "$DEPLOY_PATH" "$DEPLOY_BRANCH" "$REMOTE_PHP" "$REMOTE_COMPOSER")"
    "${SSH[@]}" "bash -s -- $remote_args"
}

# ---------------------------------------------------------------------------
info "Vorprüfungen"

if [[ -n "$(git status --porcelain)" ]]; then
    git status --short
    fail "Es gibt uncommittete Änderungen. Bitte erst committen (und pushen)."
fi

current_branch="$(git rev-parse --abbrev-ref HEAD)"
if [[ "$current_branch" != "$DEPLOY_BRANCH" ]]; then
    fail "Aktueller Branch ist '$current_branch', deployed wird aber '$DEPLOY_BRANCH'."
fi

git fetch --quiet origin "$DEPLOY_BRANCH"
if [[ "$(git rev-parse HEAD)" != "$(git rev-parse "origin/$DEPLOY_BRANCH")" ]]; then
    fail "Lokaler Stand und origin/$DEPLOY_BRANCH unterscheiden sich. Bitte erst pushen bzw. pullen."
fi

echo "OK: $(git log -1 --format='%h %s')"

# ---------------------------------------------------------------------------
info "Assets lokal bauen"

npm ci
npm run build

# ---------------------------------------------------------------------------
if [[ "$DRY_RUN" == true ]]; then
    info "Dry-Run: Diese Dateien würden nach public/build übertragen bzw. gelöscht"
    rsync -azn --delete --itemize-changes -e "ssh -p $DEPLOY_PORT" \
        public/build/ "$DEPLOY_HOST:$DEPLOY_PATH/public/build/"
    info "Dry-Run beendet, auf dem Server wurde nichts geändert."
    exit 0
fi

on_remote_error() {
    printf '\n\033[1;31mDeploy fehlgeschlagen. Die App ist evtl. noch im Wartungsmodus.\033[0m\n' >&2
    printf 'Wieder hochfahren mit:\n  ssh -p %s %s "cd %s && %s artisan up"\n' \
        "$DEPLOY_PORT" "$DEPLOY_HOST" "$DEPLOY_PATH" "$REMOTE_PHP" >&2
}
trap on_remote_error ERR

# ---------------------------------------------------------------------------
info "Server: Wartungsmodus an und Code aktualisieren"

remote <<'REMOTE'
set -Eeuo pipefail
DEPLOY_PATH="$1"; DEPLOY_BRANCH="$2"; REMOTE_PHP="$3"
cd "$DEPLOY_PATH"

if [[ ! -f .env ]]; then
    echo "Fehler: $DEPLOY_PATH/.env fehlt auf dem Server." >&2
    exit 1
fi

$REMOTE_PHP artisan down --retry=15 || true

git fetch origin "$DEPLOY_BRANCH"
git reset --hard "origin/$DEPLOY_BRANCH"
git log -1 --format='Server steht jetzt auf: %h %s'
REMOTE

# ---------------------------------------------------------------------------
info "Assets hochladen (public/build)"

rsync -az --delete -e "ssh -p $DEPLOY_PORT" \
    public/build/ "$DEPLOY_HOST:$DEPLOY_PATH/public/build/"

# ---------------------------------------------------------------------------
info "Server: Abhängigkeiten, Migrationen, Caches"

remote <<'REMOTE'
set -Eeuo pipefail
DEPLOY_PATH="$1"; REMOTE_PHP="$3"; REMOTE_COMPOSER="$4"
cd "$DEPLOY_PATH"

$REMOTE_COMPOSER install --no-dev --optimize-autoloader --no-interaction
$REMOTE_PHP artisan migrate --force
$REMOTE_PHP artisan storage:link 2>/dev/null || true
$REMOTE_PHP artisan optimize
$REMOTE_PHP artisan up
REMOTE

trap - ERR

info "Deploy erfolgreich abgeschlossen"
