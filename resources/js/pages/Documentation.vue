<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { dashboard, documentation } from '@/routes';
import { edit as appearance } from '@/routes/appearance';
import { index as handouts } from '@/routes/handouts';
import { edit as profile } from '@/routes/profile';
import { edit as security } from '@/routes/security';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Documentation',
                href: documentation(),
            },
        ],
    },
});

const sections = [
    { id: 'about', title: 'What is PADD?' },
    { id: 'account', title: 'Your account' },
    { id: 'handouts', title: 'Handouts' },
    { id: 'padd', title: 'PADD' },
    { id: 'settings', title: 'Settings' },
    { id: 'troubleshooting', title: 'Troubleshooting' },
];
</script>

<template>
    <Head title="Documentation" />

    <div class="px-4 py-6">
        <Heading
            title="Documentation"
            description="How to use Star Trek PADD"
        />

        <nav aria-label="Contents" class="max-w-2xl rounded-lg border p-4">
            <ul class="space-y-1 text-sm">
                <li v-for="section in sections" :key="section.id">
                    <a
                        :href="`#${section.id}`"
                        class="underline underline-offset-4"
                        >{{ section.title }}</a
                    >
                </li>
            </ul>
        </nav>

        <article
            class="mt-8 max-w-2xl space-y-8 text-sm leading-6 [&_a]:underline [&_a]:underline-offset-4 [&_h3]:font-medium [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-6 [&_section]:scroll-mt-6 [&_section]:space-y-3 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-6"
        >
            <section id="about">
                <h2 class="text-lg font-semibold">What is PADD?</h2>
                <p>
                    This app simulates a PADD from Star Trek. It is made for
                    game masters of the roleplaying game Star Trek Adventures
                    who want to prepare handouts for their players.
                </p>
                <p>
                    Write your handouts in advance and show them one after the
                    other on the devices of your players.
                </p>
            </section>

            <section id="account">
                <h2 class="text-lg font-semibold">Your account</h2>
                <h3>Register</h3>
                <ol>
                    <li>Select "Register" on the start page.</li>
                    <li>Enter your name, email address and a password.</li>
                    <li>
                        Open the link in the verification email. You can only
                        use your handouts after your email address is verified.
                    </li>
                </ol>
                <p>
                    If there is no "Register" button, the administrator has
                    disabled registration.
                </p>
                <h3>Forgot your password?</h3>
                <p>
                    Select "Forgot your password?" on the login page. You will
                    receive an email with a link to choose a new password.
                </p>
            </section>

            <section id="handouts">
                <h2 class="text-lg font-semibold">Handouts</h2>
                <p>
                    Open <Link :href="handouts()">Handouts</Link> in the menu to
                    see all your handouts, newest first. Other users, including
                    the administrator, including the administrator, cannot see
                    them in the app. The handout shown on the PADD is visible to
                    everyone who knows your PADD link.
                </p>
                <h3>Create a handout</h3>
                <ol>
                    <li>Select "New handout".</li>
                    <li>
                        Enter a title (up to 300 characters) and a text (up to
                        3000 characters). Both are required.
                    </li>
                    <li>
                        Choose the design for the PADD. The design of the
                        handout you created last is preselected.
                    </li>
                    <li>Select "Save".</li>
                </ol>
                <h3>Edit a handout</h3>
                <p>
                    Select the title or the pencil icon, make your changes and
                    select "Save". "Cancel" discards your changes.
                </p>
                <h3>Delete a handout</h3>
                <p>
                    Select the trash icon and confirm. A deleted handout cannot
                    be restored.
                </p>
            </section>

            <section id="padd">
                <h2 class="text-lg font-semibold">PADD</h2>
                <p>
                    The PADD shows one of your handouts in the LCARS design,
                    without any menus of this app.
                </p>
                <ol>
                    <li>
                        Open the <Link :href="dashboard()">Dashboard</Link> and
                        select "Start PADD" to open it in a new tab.
                    </li>
                    <li>
                        To use the devices of your players, scan the QR code
                        below the button or copy the link and open it there. No
                        login is required, so share it only with your players.
                    </li>
                    <li>
                        Select the eye icon next to a handout to show it. It
                        replaces the previous one within a few seconds. Select
                        the icon again to hide the handout.
                    </li>
                </ol>
                <p>
                    If no handout is shown, the PADD displays "Standby". Changes
                    to the shown handout appear automatically as well.
                </p>
            </section>

            <section id="settings">
                <h2 class="text-lg font-semibold">Settings</h2>
                <p>Open the menu with your name and select "Settings".</p>
                <ul>
                    <li>
                        <Link :href="profile()">Profile</Link>: change your name
                        and email address. A new email address has to be
                        verified again.
                    </li>
                    <li>
                        <Link :href="security()">Security</Link>: change your
                        password.
                    </li>
                    <li>
                        <Link :href="appearance()">Appearance</Link>: choose the
                        light, dark or system theme.
                    </li>
                </ul>
                <p>
                    You can also delete your account on the profile page. This
                    permanently deletes all your handouts as well.
                </p>
            </section>

            <section id="troubleshooting">
                <h2 class="text-lg font-semibold">Troubleshooting</h2>
                <ul>
                    <li>
                        No verification email? Check your spam folder. You can
                        request a new email on the verification page.
                    </li>
                    <li>
                        Too many login attempts? Wait a minute and try again.
                    </li>
                    <li>
                        Page not found when opening a handout? It was deleted or
                        belongs to another user.
                    </li>
                </ul>
            </section>
        </article>
    </div>
</template>
