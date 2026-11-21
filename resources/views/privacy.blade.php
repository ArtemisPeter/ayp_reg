@extends('layouts.portal')

@section('title', 'Privacy Notice')

@section('content')
    <div class="duyog-card">
        <div class="card-body">
            <h1 class="h3 mb-1 duyog-heading" style="font-size: 2rem;">Privacy Notice</h1>
            <p class="text-muted">South Mindanao District, Alliance Youth of the Philippines (CAMACOP) &middot; DUYOG Youth Camp</p>

            <p>This notice explains what personal information the DUYOG registration system holds about you, why,
                who can see it, and what you can ask us to do with it, in line with the Data Privacy Act of 2012
                (Republic Act No. 10173).</p>

            <h2 class="h5 mt-4">Who is responsible</h2>
            <p>The South Mindanao District of the Alliance Youth of the Philippines (SMD AYP), CAMACOP, runs this
                system and is responsible for the information in it.</p>

            <h2 class="h5 mt-4">What we collect</h2>
            <ul>
                <li>Your first, middle and last name</li>
                <li>Your Local Church and Circuit</li>
                <li>Your birthday</li>
                <li>Whether you have been baptised</li>
                <li>Whether you are attending as Youth or as a Pastor, and whether you registered in advance or on-site</li>
                <li>At the camp, the times your Camp ID is scanned in and out at the security gate</li>
            </ul>
            <p>Your birthday and baptism status can be considered sensitive personal information, so we use them only
                for the purposes below. If you are under 18, please make sure your parent or guardian agrees to your
                registration before your Local Church sends in your details.</p>

            <h2 class="h5 mt-4">Why we collect it</h2>
            <ul>
                <li>To register you for the Youth Camp and confirm your Local Church and Circuit</li>
                <li>To print your Camp ID</li>
                <li>To run the security gate, so we know who is inside the camp</li>
                <li>To count attendance per Circuit and record contest results</li>
            </ul>
            <p>The QR code on your Camp ID holds only a random code, never your name or other details. The code is
                looked up in our system when it is scanned.</p>

            <h2 class="h5 mt-4">Who can see it</h2>
            <ul>
                <li><strong>Your Circuit President</strong> can see and correct the details of Attendees from their own Circuit only.</li>
                <li><strong>District staff</strong> (the Secretary and the Admin) can see and manage every Attendee's details.</li>
                <li><strong>Security</strong> sees only your first name, Local Church and Circuit when your Camp ID is scanned. So the gate still works without internet, the scanning phone keeps a copy of these three details for every Attendee during the camp.</li>
                <li><strong>The public website</strong> shows only how many Youth each Circuit has registered, never any names.</li>
            </ul>
            <p>We do not sell your information or share it with anyone outside SMD AYP.</p>

            <h2 class="h5 mt-4">How we protect it</h2>
            <p>Only staff with their own username and password can sign in, and each person sees only what their role
                needs. Accounts are locked after repeated wrong passwords, and staff must replace the temporary
                password they are given.</p>

            <h2 class="h5 mt-4">How long we keep it</h2>
            <p>We keep your information for as long as the District needs it to run the camp and keep its records.
                You can ask us at any time to remove your personal details, as explained below.</p>

            <h2 class="h5 mt-4">Your rights</h2>
            <p>You have the right to:</p>
            <ul>
                <li>be told what information we hold about you, and get a copy of it</li>
                <li>have wrong or incomplete information corrected</li>
                <li>have your information removed, or object to how it is used</li>
                <li>file a complaint with the National Privacy Commission (<a href="https://privacy.gov.ph" target="_blank" rel="noopener">privacy.gov.ph</a>)</li>
            </ul>

            <h2 class="h5 mt-4">Questions or concerns</h2>
            <p>If you have questions about your information, or want it corrected or removed, contact the
                <strong>SMD AYP Page</strong>.</p>
        </div>
    </div>
@endsection
