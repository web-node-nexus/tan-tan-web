<?php

namespace App\Support;

/**
 * Long-form marketing / informative copy for the public site.
 * Kept here so Blade stays readable and content can grow without tiny stubs.
 */
final class SiteCopy
{
    /** @return list<string> */
    public static function metroCities(): array
    {
        return [
            'Mumbai', 'Delhi NCR', 'Bengaluru', 'Hyderabad', 'Chennai', 'Kolkata',
            'Pune', 'Ahmedabad', 'Jaipur', 'Surat', 'Lucknow', 'Kanpur',
            'Nagpur', 'Indore', 'Bhopal', 'Patna', 'Chandigarh', 'Kochi',
            'Coimbatore', 'Vadodara', 'Nashik', 'Rajkot', 'Varanasi', 'Guwahati',
            'Bhubaneswar', 'Visakhapatnam', 'Madurai', 'Mysuru', 'Thiruvananthapuram', 'Ranchi',
            'Amritsar', 'Jodhpur', 'Raipur', 'Dehradun', 'Mangaluru', 'Aurangabad',
            'Jabalpur', 'Gwalior', 'Allahabad / Prayagraj', 'Agra', 'Meerut', 'Faridabad',
            'Ghaziabad', 'Noida', 'Gurugram', 'Thane', 'Navi Mumbai', 'Howrah',
        ];
    }

    /**
     * @deprecated Use FaqCopy::groups() — kept for any older references.
     * @return array<string, array{intro: string, items: list<array{q: string, a: list<string>, bullets?: list<string>}>}>
     */
    public static function faqGroups(): array
    {
        return FaqCopy::groups();
    }

    /** @return list<array{name: string, blurb: string, subs: string, fields: string, tips: string}> */
    public static function categoriesDetailed(): array
    {
        return [
            [
                'name' => 'Vehicles',
                'blurb' => 'Cars, bikes, scooters, bicycles and light commercial vehicles from owners and local dealers who follow Tan Tan policies. Listings work best with honest kilometres, service notes and clear daylight photos of all sides plus the odometer.',
                'subs' => 'Cars · Motorcycles · Scooters · Bicycles · Commercial · Spare parts (where allowed)',
                'fields' => 'Brand, model, year, kilometres, fuel type, transmission, number of owners, insurance validity, registration state',
                'tips' => 'Ask for RC, service history and a cold start video. Prefer daytime meetups at service centres or busy parking lots. Never transfer money before seeing the vehicle. Match chassis/engine numbers carefully on higher-value deals.',
            ],
            [
                'name' => 'Property',
                'blurb' => 'Flats, houses, PG/hostel beds, plots and commercial spaces for sale or rent — with location honesty as a first-class filter. Misleading pins and bait rents are moderated aggressively.',
                'subs' => 'For rent · For sale · PG / Roommate · Plots · Office / Shop',
                'fields' => 'Type, BHK, bathrooms, furnished status, carpet/built-up area, deposit, monthly rent, facing, floor, parking',
                'tips' => 'Visit in person, verify ownership/brokerage in writing, and beware of listing fees demanded before viewing. Tan Tan does not collect rent on your behalf. Never share OTPs for “owner verification” links.',
            ],
            [
                'name' => 'Mobiles',
                'blurb' => 'Smartphones and accessories with clear condition photos — screen, edges, box and bill when available. Battery health notes and warranty left help serious buyers filter faster.',
                'subs' => 'Android · iPhone · Feature phones · Accessories · Wearables',
                'fields' => 'Brand, model, storage, RAM, battery health notes, warranty left, bill available, box available',
                'tips' => 'Check IMEI, activation lock, and run a quick call/camera/mic test at meetup. Prefer UPI that reflects instantly over screenshot “proof”. Factory reset only after payment clears.',
            ],
            [
                'name' => 'Electronics',
                'blurb' => 'Laptops, TVs, cameras, audio gear, consoles and home appliances with age and warranty fields. Power-on demos at meetup prevent most disputes.',
                'subs' => 'Laptops · TVs · Cameras · Audio · Appliances · Gaming',
                'fields' => 'Brand, model, age, warranty, accessories included, condition notes',
                'tips' => 'Power on devices, check ports, and confirm original chargers. For laptops, verify storage wipe and login locks are removed. Keep serial numbers in chat for expensive gear.',
            ],
            [
                'name' => 'Furniture',
                'blurb' => 'Sofas, beds, wardrobes, tables and décor — photo-first so buyers can judge size and wear before travelling. Dimensions in the description save failed lift attempts.',
                'subs' => 'Living · Bedroom · Dining · Office · Décor',
                'fields' => 'Material, dimensions, set pieces, condition, dismantling help offered',
                'tips' => 'Measure lifts and doorways. Ask about termite/pest history for wood furniture. Factor transport cost into your offer. Confirm who brings tools for dismantling.',
            ],
            [
                'name' => 'Fashion',
                'blurb' => 'Clothing, footwear, watches and bags — especially unused or barely used pieces with honest photos. Authenticity claims for premium brands should be backed by bills or serials when possible.',
                'subs' => 'Men · Women · Kids · Watches · Bags · Footwear',
                'fields' => 'Size, brand, gender, fabric, unused/used, bill available',
                'tips' => 'Confirm size charts and authenticity for premium brands. Meet in public; inspect stitching and serials. No returns are enforced by Tan Tan — agree terms in chat if you offer any.',
            ],
            [
                'name' => 'Jobs',
                'blurb' => 'Local full-time, part-time, internship and freelance openings posted by employers and individuals. Tan Tan is not a payroll provider — treat “registration fee” demands as scam signals.',
                'subs' => 'Full-time · Part-time · Internship · Freelance · Temporary',
                'fields' => 'Role, salary range, experience, job type, company/individual, work mode',
                'tips' => 'Never pay for a job offer. Interview in known offices or video calls. Report “registration fee” scams immediately. Cross-check company existence independently.',
            ],
            [
                'name' => 'Services',
                'blurb' => 'Electricians, tutors, cleaners, event help, movers and other neighbourhood services with area coverage. Clear pricing models reduce haggling fatigue.',
                'subs' => 'Home repair · Tutors · Beauty · Events · Transport · Other',
                'fields' => 'Service type, availability, pricing model, areas covered, experience',
                'tips' => 'Agree scope and price in Tan Tan chat before work starts. Prefer staged payments tied to milestones. Photos of past work help, but still verify in person for high-value jobs.',
            ],
            [
                'name' => 'Pets',
                'blurb' => 'Pets and pet accessories only where legally allowed. Sellers should share vaccination and care details honestly. Illegal wildlife trade is banned and reportable.',
                'subs' => 'Dogs · Cats · Birds · Fish · Accessories · Other',
                'fields' => 'Breed, age, vaccination, papers, gender',
                'tips' => 'Follow local animal welfare rules. Avoid impulse purchases; ask for vet records. Report illegal wildlife trade. Meet in safe, ventilated spaces; do not transport animals unsafely.',
            ],
            [
                'name' => 'Books & hobbies',
                'blurb' => 'Textbooks, novels, instruments, sports gear and collectibles — great for students and hobbyists. Edition and condition notes matter for exam books.',
                'subs' => 'Books · Musical instruments · Sports · Collectibles · Art',
                'fields' => 'Title/author, subject, language, condition, brand (for gear)',
                'tips' => 'Check editions for textbooks. For instruments, test in person before paying. Bundle deals (notes + books) should list every item in the description.',
            ],
            [
                'name' => 'Kids & baby',
                'blurb' => 'Strollers, cribs, toys and gear — condition and safety notes matter more than brand hype. Recall and harness integrity should be checked at meetup.',
                'subs' => 'Gear · Toys · Clothing · Nursery',
                'fields' => 'Age group, brand, safety notes, condition',
                'tips' => 'Inspect harnesses, brakes and expiry on baby products. Clean thoroughly before use. Ask about smoke/pet exposure if your child has allergies.',
            ],
            [
                'name' => 'Other',
                'blurb' => 'Legal items that do not fit neatly elsewhere. Clear titles and photos are mandatory. If a better category exists, prefer it so filters work for buyers.',
                'subs' => 'Misc · Bulk household · Seasonal',
                'fields' => 'Flexible — write a precise description and add measurements where useful',
                'tips' => 'If unsure whether an item is allowed, check Safety tips / Terms or ask support before posting. “Message for price” without photos usually performs poorly and may be moderated.',
            ],
        ];
    }

    /**
     * @return list<array{0: string, 1: list<string>}>
     */
    public static function termsSections(): array
    {
        return [
            ['1. Acceptance of these Terms', [
                'By creating an account, browsing listings, posting ads, chatting, purchasing packages, or otherwise using Tan Tan’s mobile applications, website, or related services (together, the “Service”), you agree to these Terms of Use and our Privacy Policy.',
                'If you do not agree, do not use the Service. You represent that you are at least 18 years old (or the age of majority in your state) when you complete offline transactions, and that you have legal capacity to enter contracts. Guardians are responsible for devices used by minors in their household.',
                'We may update these Terms from time to time. The “Last updated” date at the top of this page will change when we do. Continued use after an update constitutes acceptance of the revised Terms, except where applicable law requires a different process.',
            ]],
            ['2. What Tan Tan is (and is not)', [
                'Tan Tan is a classifieds marketplace that helps users discover, list and communicate about goods, property, jobs and local services. We provide software tools: listings, search, chat, offers, reports, optional paid visibility packages, and related features.',
                'Tan Tan is not a party to the contract of sale between buyers and sellers. We do not take possession of user inventory, do not escrow purchase money for goods by default, and do not guarantee the quality, legality, safety, or delivery of items listed by users.',
                'Optional package payments (Bump, Featured, Top Ad, extra ad limits, seasonal bundles) are processed via our payment partner (for example Razorpay). Those payments are for marketplace visibility services, not for the underlying goods.',
            ]],
            ['3. Accounts & eligibility', [
                'Provide accurate registration information and keep it updated. Keep OTPs and passwords confidential. You are responsible for activity under your account unless you notify us of unauthorised access promptly.',
                'Do not create multiple accounts to evade bans, ad limits, negative ratings, or investigations. Do not buy, sell, or rent Tan Tan accounts. We may require re-verification of phone/email at any time.',
                'We may suspend, restrict, or terminate accounts that violate these Terms, harm other users, abuse reports, attempt fraud, scrape the Service, or create security risk. Appeals may be sent to support@tantan.app with evidence.',
            ]],
            ['4. Listings & acceptable use', [
                'Listings must be accurate, lawful, and placed in the correct category. Photos should depict the actual item. Prices in ₹ should not be bait-and-switch. Location should reflect where meetup/handover can reasonably happen.',
                'Prohibited content and items include, without limitation: illegal drugs and paraphernalia, weapons and explosives, stolen goods, counterfeit goods, content that exploits minors, hate or harassment, malware/phishing, and anything banned under Indian law or Tan Tan policies published in-app or on this site.',
                'We may remove or hide content, limit distribution, require edits, or disable promotions on violating listings. Repeated community reports may auto-hide a listing pending review. Unused promotion time on removed violating listings may be forfeited.',
            ]],
            ['5. Chat, offers & contact details', [
                'Use chat and Make Offer to negotiate in good faith. Do not send spam, threats, sexual harassment, or links designed to steal credentials or OTPs. Do not upload malware images or illegal content in chat.',
                'Phone numbers remain masked by default. If you enable Show phone, Call/WhatsApp actions may appear to others; you accept the privacy trade-off of that choice. Once a number is shared offline, Tan Tan cannot control how others store it.',
                'Offers accepted in chat create a record between users but do not make Tan Tan a party to the deal or a guarantor of payment or delivery.',
            ]],
            ['6. Paid packages & billing', [
                'Featured placement, Top Ad, Bump Up, extra ad limits and seasonal bundles are optional. Prices are shown in Indian Rupees in the app and may change. The checkout screen price at the time of payment is authoritative.',
                'Activation of a promotion requires verified payment (typically via payment-provider webhooks). Screenshots of UPI apps are not proof for activation. Packages are non-transferable between accounts and apply only to the listing and duration selected.',
                'Boosts improve relative visibility; they do not guarantee views, chats, or a sale. Refunds for time already consumed on an active promotion are generally unavailable. If payment succeeded but activation failed for a technical reason attributable to Tan Tan, contact support with the payment ID for a remedy (re-apply boost or refund) at our reasonable discretion under applicable law.',
            ]],
            ['7. Reviews, ratings & reputation', [
                'Reviews must reflect genuine experiences. Fake reviews, coordinated rating rings, bribery for stars, or threats to leave negative reviews unless paid (“review extortion”) are prohibited and may be removed.',
                'We may limit review visibility or reset abusive reputation signals when investigating fraud. Attempting to coerce reviews outside the product still violates these Terms.',
            ]],
            ['8. Intellectual property & licence to user content', [
                'Tan Tan branding, logos, UI, software and documentation are owned by Tan Tan or its licensors and are protected by applicable IP laws. You may not copy, scrape, reverse engineer (except to the limited extent permitted by law), or frame the Service without permission.',
                'You retain ownership of content you upload (photos, text, etc.). You grant Tan Tan a worldwide, non-exclusive, royalty-free licence to host, store, reproduce, adapt for formatting, and display that content as needed to operate, moderate, promote (including featuring your listing when you purchase a package), and improve the Service.',
                'You represent that you have the rights to grant this licence and that your content does not infringe others’ rights. We may remove content alleged to infringe upon notice consistent with applicable law.',
            ]],
            ['9. Disclaimers & limitation of liability', [
                'THE SERVICE IS PROVIDED ON AN “AS IS” AND “AS AVAILABLE” BASIS. To the maximum extent permitted by Indian law, Tan Tan disclaims warranties of merchantability, fitness for a particular purpose, and non-infringement.',
                'You acknowledge that offline meetings and payments between users carry inherent risk. Tan Tan is not liable for user-to-user disputes, item defects, accidents at meetups, or losses from ignoring Safety tips — except where liability cannot be excluded under applicable consumer or other mandatory law.',
                'Where liability can be limited, Tan Tan’s aggregate liability related to package purchases will not exceed the amount you paid to Tan Tan for the specific package giving rise to the claim in the three months preceding the claim. Nothing in these Terms limits liability for fraud, wilful misconduct, or other liability that cannot be limited by law.',
            ]],
            ['10. Indemnity', [
                'You agree to indemnify and hold harmless Tan Tan and its officers, employees and agents from claims, damages and expenses (including reasonable legal fees) arising from your listings, your offline conduct, your violation of these Terms, or your infringement of third-party rights — to the extent permitted by law.',
            ]],
            ['11. Governing law & disputes', [
                'These Terms are governed by the laws of India. Subject to mandatory consumer protections, courts with competent jurisdiction at the place of Tan Tan’s primary Indian operating entity shall have jurisdiction. You may also have rights to approach consumer forums as provided by law.',
                'Before filing a formal dispute, email legal@tantan.app with a clear description and supporting documents so we can attempt a good-faith resolution.',
            ]],
            ['12. Contact', [
                'Legal: legal@tantan.app. Support: support@tantan.app. Privacy: privacy@tantan.app. Or use the Contact form on this website. Partnerships/press: hello@tantan.app.',
            ]],
        ];
    }

    /**
     * @return list<array{0: string, 1: list<string>}>
     */
    public static function privacySections(): array
    {
        return [
            ['1. Who we are & scope', [
                'Tan Tan operates a classifieds marketplace for users in India. This Privacy Policy explains what personal data we collect through our apps, website and related services, why we collect it, how long we keep it, with whom we share it, and the choices you have.',
                'Privacy requests and questions: privacy@tantan.app. If you cannot access the app, use the Delete Account page or email support@tantan.app for account deletion help.',
                'This Policy should be read with our Terms of Use. Capitalised product features (My Ads, Make Offer, etc.) refer to in-app functionality that may evolve; the principles here still apply.',
            ]],
            ['2. Data we collect', [
                'Account data: name, phone number, email, profile photo, preferred language/locale, and authentication identifiers (including Google Sign-In tokens if you choose that method).',
                'Marketplace data: listings (titles, descriptions, photos, category fields, prices, status), favourites, saved/recent searches, offers, reports, blocks, and ratings/reviews you submit or receive.',
                'Communication data: chat messages and images needed to deliver realtime messaging and moderation. Keep chats civil — report abuse rather than sharing sensitive personal documents unnecessarily.',
                'Location data: approximate or precise location you grant via GPS or the city/area you select manually. We use this for nearby ranking and map distance, not to publish your live trail to other users as a stalking feature.',
                'Device & usage data: device type, app version, crash/diagnostic logs, IP address, and push notification tokens. Analytics may be aggregated to improve performance.',
                'Payment data: for package purchases we receive payment identifiers and status from Razorpay (or successor providers). We do not store full card PAN/CVV on Tan Tan servers.',
                'Safety signals: report reasons, block lists, and moderator notes required to investigate abuse.',
            ]],
            ['3. How we use data', [
                'To create and secure your account, send OTPs, and prevent account takeover.',
                'To show and rank listings, personalise nearby recommendations, and power search/filters.',
                'To deliver chat, offers, notifications, and My Ads insights.',
                'To process package payments, show transaction history, and generate invoices.',
                'To moderate content, detect fraud/spam, enforce Terms, and respond to lawful requests.',
                'To improve reliability, fix bugs, and understand feature usage in aggregate.',
                'To communicate service messages (security, expiry reminders, policy updates). Marketing emails/SMS, if any, will include unsubscribe where required.',
            ]],
            ['4. Phone visibility & public profile', [
                'Phone numbers are masked by default. Call and WhatsApp actions appear on a listing only if the seller enables Show phone.',
                'Your public-facing profile may show display name, photo, ratings, verification badge (if any), and listings you publish. Do not put Aadhaar, full address, or bank details in listing photos or descriptions.',
                'Once you share a number or address in chat or offline, the recipient may store that information outside Tan Tan. Share the minimum needed for a safe meetup.',
            ]],
            ['5. Sharing & processors', [
                'We share data with: infrastructure and hosting providers, analytics/crash tools, SMS/OTP providers, push notification services, and payment processors — only as needed to run the Service under appropriate contracts.',
                'Other users see listing content you publish and messages you send them. Moderators see reports and relevant evidence.',
                'We may disclose information to authorities when legally required or to protect users from serious harm.',
                'We do not sell personal data to data brokers. If we ever engage in a merger or asset transfer, user data may transfer under continued protection commitments, with notice where required.',
            ]],
            ['6. Retention', [
                'We retain account and marketplace data while your account is active and as needed to provide the Service.',
                'After account deletion, we aim to remove or anonymise primary personal stores promptly (target within 30 days), except where retention is required for legal, tax, accounting, dispute, or fraud-prevention purposes.',
                'Backups may lag primary deletion by a short period and are then expired on the backup cycle. Anonymised analytics may persist without identifying you.',
            ]],
            ['7. Security', [
                'We use HTTPS in transit, access controls for admin tools, OTP rate limits, and upload validation. Passwords (where used) are stored hashed; OTP flows prefer one-time codes over long-lived shared secrets.',
                'No method of transmission or storage is perfectly secure. Protect your device with a lock screen, never share OTPs, and beware of phishing sites that imitate Tan Tan.',
                'If you believe your account was compromised, contact support@tantan.app immediately from a safe channel and stop using compromised devices for payments.',
            ]],
            ['8. Your choices & rights', [
                'Edit profile fields, control notification categories, and toggle Show phone in the app.',
                'Revoke location permission in your operating system settings, or switch to manual city selection.',
                'Delete your account in-app (Profile → Settings → Delete account) or via the Delete Account page / support email if you cannot sign in.',
                'Request help accessing or correcting data by emailing privacy@tantan.app. We will respond within timelines required by applicable Indian law, after verifying your identity.',
                'You may have additional rights under the Digital Personal Data Protection Act, 2023 and other applicable laws as they come into force; we will update this Policy and in-app flows accordingly.',
            ]],
            ['9. Children', [
                'Tan Tan is not directed at children under 18. We do not knowingly create accounts for children to complete marketplace transactions. If you believe a minor created an account, contact privacy@tantan.app and we will take appropriate steps.',
            ]],
            ['10. International users', [
                'The Service is designed for India. If you access it from outside India, you do so on your own initiative; processing may occur on servers in India or in regions used by our processors. Cross-border shipping features are not part of the current product.',
            ]],
            ['11. Changes to this Policy', [
                'We may update this Privacy Policy. Material changes will be indicated by a new “Last updated” date and, where appropriate, an in-app notice. Continued use after the effective date means you acknowledge the updated Policy.',
            ]],
            ['12. Contact', [
                'privacy@tantan.app for privacy requests · support@tantan.app for account access problems · legal@tantan.app for legal notices · Contact form on this website for general help.',
            ]],
        ];
    }
}
