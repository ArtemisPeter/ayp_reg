@extends('layouts.portal')

@section('title', 'Privacy Notice')

@section('content')
    <div class="duyog-card">
        <div class="card-body">
            <h1 class="h3 mb-1 duyog-heading" style="font-size: 2rem;">Privacy Notice</h1>
            <p class="text-muted">South Mindanao District, CAMACOP &middot; DUYOG '26</p>

            <p class="fw-semibold mb-4 text-danger">
                Draft for SMD to review before this system is used publicly. Replace the [bracketed] contact
                details below with a real name, email, or phone number before publishing this page.
            </p>

            <h2 class="h5 mt-4">What we collect</h2>
            <p>When you are registered for a Youth Camp, we collect your first, middle and last name, your Local
                Church and Circuit, your birthday, whether you have been baptised, and whether you are registering
                as Youth or as a Pastor. If you attend, the security gate also records the time you enter and
                leave the camp, matched to your Camp ID.</p>

            <h2 class="h5 mt-4">Why we collect it</h2>
            <p>This information is used to register you for the Youth Camp, print your Camp ID, run the security
                gate, and produce attendance counts per Circuit. Your Camp ID's QR code holds only a random code,
                never your name or other details — the code is looked up on our system when scanned.</p>

            <h2 class="h5 mt-4">Who can see it</h2>
            <ul>
                <li>Your Circuit President can see your information alongside other Attendees from your Circuit.</li>
                <li>District staff (the Secretary and Admin) can see every Attendee's information.</li>
                <li>Security, at the gate, sees only your name, Local Church and Circuit when your Camp ID is scanned.</li>
                <li>The public website shows only how many Youth each Circuit has registered — never any names.</li>
            </ul>

            <h2 class="h5 mt-4">How long we keep it</h2>
            <p>We keep your information for as long as needed to run the current Youth Camp, and to let you reuse
                last year's details when you register again. After the following year's Pre-Registration closes,
                names, birthdays and other personal details are removed; attendance counts and contest results are
                kept without any names attached.</p>

            <h2 class="h5 mt-4">Questions or concerns</h2>
            <p>If you have questions about your information, or want it corrected or removed, contact
                [SMD contact name], [SMD contact email or phone number].</p>
        </div>
    </div>
@endsection
