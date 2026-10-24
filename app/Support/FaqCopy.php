<?php

namespace App\Support;

/**
 * Long-form FAQ for the public site — multi-paragraph answers, not one-liners.
 *
 * Shape per group:
 *   intro: string
 *   items: list of { q, a: list<string>, bullets?: list<string> }
 */
final class FaqCopy
{
    /** @return list<array{q: string, topic: string}> */
    public static function popular(): array
    {
        return [
            ['q' => 'Is Tan Tan free to download and use?', 'topic' => 'Getting started'],
            ['q' => 'Do I need two accounts — one to buy and one to sell?', 'topic' => 'Getting started'],
            ['q' => 'How does the Post Ad flow work?', 'topic' => 'Posting ads'],
            ['q' => 'Why can’t I see the seller’s phone number?', 'topic' => 'Chat & offers'],
            ['q' => 'What do packages cost?', 'topic' => 'Payments & packages'],
            ['q' => 'How do I delete my account?', 'topic' => 'Privacy & deletion'],
            ['q' => 'What should I check before meeting a seller?', 'topic' => 'Buying & search'],
            ['q' => 'I think I was scammed offline — what now?', 'topic' => 'Trust & safety'],
        ];
    }

    /**
     * @return array<string, array{intro: string, items: list<array{q: string, a: list<string>, bullets?: list<string>}>}>
     */
    public static function groups(): array
    {
        return [
            'Getting started' => [
                'intro' => 'New to Tan Tan? Start here. These answers cover what the product is, what is free, how onboarding works, and how the public website relates to the mobile app.',
                'items' => [
                    [
                        'q' => 'What is Tan Tan in one paragraph?',
                        'a' => [
                            'Tan Tan is a photo-first classifieds marketplace for Indian neighbourhoods. One mobile account lets you browse nearby deals, favourite items, chat with sellers, make structured offers, and also post your own ads — furniture, phones, scooters, rentals, jobs, local services and more.',
                            'The public website (this site) explains the product, safety, packages and legal policies. Posting, chatting and paying for boosts happen in the Android/iOS app so camera, maps, push notifications and secure storage work properly.',
                        ],
                    ],
                    [
                        'q' => 'Is Tan Tan free to download and use?',
                        'a' => [
                            'Yes for core use. Downloading, creating an account, browsing listings, favouriting, chatting, making offers, and posting within your free monthly ad limit are free. There is no subscription required to keep a normal personal account active.',
                            'You only pay if you choose an optional visibility package (Bump, Featured, Top Ad), a seasonal bundle, or an extra-ad limit pack. Payment for the goods themselves (the phone, sofa, scooter) always happens between you and the other person offline — Tan Tan is not the merchant of that sale.',
                        ],
                        'bullets' => [
                            'Free: browse, chat, post within limits',
                            'Paid (optional): boosts & extra ad packs',
                            'Goods money: buyer ↔ seller directly',
                        ],
                    ],
                    [
                        'q' => 'Do I need two accounts — one to buy and one to sell?',
                        'a' => [
                            'No. Every Tan Tan account is both buyer and seller by design. You can favourite a fridge in the morning and post your old scooter in the evening from the same profile and the same chat inbox.',
                            'Favourites, My Ads, chats, offers, reviews and settings all live under one login. Creating a second account just to “look like a buyer” usually backfires for trust and can violate the Terms if used to evade limits or bans.',
                        ],
                    ],
                    [
                        'q' => 'What happens after I install the app?',
                        'a' => [
                            'Typical first session: pick English or हिन्दी → sign in with +91 OTP (or Google, then link a phone) → set location via GPS or city/area search → optionally allow notifications → land on Home with nearby recommendations and Featured ads.',
                            'You can skip optional permissions and set them later in Profile → Settings. If location is denied, browsing still works but “near you” ranking is weaker — switch to a manual city instead of leaving location blank forever.',
                        ],
                        'bullets' => [
                            'Language → Auth → Location → Home',
                            'Notifications optional but useful for chat',
                            'Same account on a new phone after re-login',
                        ],
                    ],
                    [
                        'q' => 'Which login methods are supported?',
                        'a' => [
                            'Primary: Indian mobile number (+91) with a 6-digit OTP. Secondary: email and Google Sign-In where enabled. We strongly recommend linking a phone even if you start with Google.',
                            'Most classifieds trust signals in India are phone-based. Buyers who see Show phone enabled expect a reachable number. Account recovery, deletion verification and fraud checks are also easier with a verified phone on file.',
                        ],
                    ],
                    [
                        'q' => 'Can I use Tan Tan in Hindi?',
                        'a' => [
                            'Yes. Choose हिन्दी on onboarding or later in Profile → Settings → Language. UI strings, many system messages and relative times follow your locale.',
                            'Devanagari uses a dedicated font stack so Hindi does not look cramped on small screens. Listing titles and descriptions stay in whatever language you type — write in the language your neighbourhood actually reads.',
                        ],
                    ],
                    [
                        'q' => 'Is there a desktop website to post ads?',
                        'a' => [
                            'Not for posting. This website is informative: About, How it works, Buyer/Seller guides, Safety, Categories, Packages explanation, FAQ, Terms, Privacy, Delete account, Contact and Download.',
                            'Posting, realtime chat, offers, My Ads, Razorpay checkout and push alerts live in the mobile app. That split is intentional — camera, location and notifications are unreliable in a generic desktop browser flow for local classifieds.',
                        ],
                    ],
                    [
                        'q' => 'Does Tan Tan work on low-end Android phones?',
                        'a' => [
                            'That is a design goal. We target smooth scrolling, compressed image uploads, restrained animation, and readable type on mid-range and lower-end devices common in India.',
                            'A development or store build is recommended for full native features (maps, secure storage, payments). Extremely old OS versions may drop support over time — check the store listing for the minimum Android/iOS version at launch.',
                        ],
                    ],
                    [
                        'q' => 'Do I need internet all the time?',
                        'a' => [
                            'Browsing new ads, chat, publishing and payments need a connection. Some recently viewed content may cache briefly, but Tan Tan is not a fully offline marketplace.',
                            'If a publish fails mid-upload, retry on stable Wi‑Fi. Prefer Wi‑Fi for large photo sets. Failed package payments do not activate boosts — check your UPI/bank app and retry.',
                        ],
                    ],
                    [
                        'q' => 'Can family members share one account?',
                        'a' => [
                            'We recommend one adult per account. Shared logins make ownership hard to prove for disputes, deletion requests, payment invoices and fraud investigations. They can also look like account takeover.',
                            'If two people in a household both sell regularly, each should verify their own +91 number. You can still help each other take photos — just publish from the correct account.',
                        ],
                    ],
                ],
            ],

            'Account & profile' => [
                'intro' => 'How profiles, verification, ratings and account recovery work — and what other people can see about you.',
                'items' => [
                    [
                        'q' => 'What do other users see on my public profile?',
                        'a' => [
                            'Typically: display name, profile photo, member-since, ratings/reviews, verification badge (if any), and your active listings. They do not see your full phone by default.',
                            'Do not put Aadhaar, full home address, bank details or OTPs in your bio, listing photos or chat. Share the minimum needed for a safe public meetup.',
                        ],
                    ],
                    [
                        'q' => 'How do I change my name, photo or language?',
                        'a' => [
                            'Open Profile → edit profile / Settings. Name and photo affect how you appear on listings and in chat. Language switches UI strings between English and हिन्दी.',
                            'If a change fails to save, check connectivity and try again. Contact support with your registered phone if the profile stays stuck after multiple attempts.',
                        ],
                    ],
                    [
                        'q' => 'What is a verified badge?',
                        'a' => [
                            'A trust signal managed by Tan Tan after checks configured by the admin team (for example phone strength or other reviews). It is visible on profiles and listing seller cards when awarded.',
                            'It is not a guarantee of a perfect deal. Still inspect items, meet in public, and follow Safety tips. Text that says “VERIFIED DEALER” in a description is meaningless if the official badge is missing.',
                        ],
                    ],
                    [
                        'q' => 'I lost my phone / SIM — how do I recover the account?',
                        'a' => [
                            'If you still have Google/email login linked, sign in that way and update your phone when possible. If OTP to the old SIM is the only method, email support@tantan.app with proof of ownership (name, old number, city, recent listing titles, invoice IDs).',
                            'We may ask clarifying questions before moving the account. Never share OTPs with anyone claiming to be “Tan Tan recovery” on WhatsApp — official support will not ask you to forward OTPs to strangers.',
                        ],
                    ],
                    [
                        'q' => 'Can I change my registered phone number?',
                        'a' => [
                            'Where the product allows phone change, you will verify the new number with OTP and may need to confirm the old one. If the flow is not available yet or is blocked for fraud risk, contact support with both numbers and a reason.',
                            'Changing numbers to evade bans or report history violates the Terms and can result in both numbers being restricted.',
                        ],
                    ],
                    [
                        'q' => 'How do ratings and reviews work?',
                        'a' => [
                            'After meaningful interactions, users can leave ratings/reviews that appear on profiles. They help future buyers judge responsiveness and honesty.',
                            'Fake reviews, bribery for stars, coordinated rating rings, or threats to leave a negative review unless paid (“review extortion”) violate the Terms and may be removed. Review the experience you actually had.',
                        ],
                    ],
                ],
            ],

            'Posting ads' => [
                'intro' => 'Category → photos → details → review. Everything about creating a listing that actually gets serious chats.',
                'items' => [
                    [
                        'q' => 'How does the Post Ad flow work?',
                        'a' => [
                            'Four steps: (1) Category & subcategory, (2) Photos with reorder + compression, (3) Details including dynamic category fields, price, condition and location, (4) Review & publish.',
                            'You may see Pending if moderation is required. Completed ads appear under My Ads. Where drafts are supported, you can leave and resume; otherwise finish in one sitting on stable internet so uploads do not fail halfway.',
                        ],
                        'bullets' => [
                            'Step 1 — right category unlocks the right filters',
                            'Step 2 — cover photo is the first buyer impression',
                            'Step 3 — empty required fields block publish',
                            'Step 4 — preview before you go live',
                        ],
                    ],
                    [
                        'q' => 'What am I allowed to sell?',
                        'a' => [
                            'Legal personal and household goods, vehicles, property/rentals where permitted, electronics, furniture, fashion, books, hobbies, kids’ items, and local services/jobs.',
                            'Prohibited examples: illegal drugs, weapons, explosives, stolen goods, counterfeits, wildlife violations, sexual content involving minors, and anything banned under Indian law or Tan Tan policies. When unsure, read Safety tips / Terms or ask support before posting.',
                        ],
                    ],
                    [
                        'q' => 'Why are there extra fields for cars or flats?',
                        'a' => [
                            'Category fields (brand, km, fuel, BHK, furnished, deposit, storage, etc.) power buyer filters and help your ad appear in the right searches. A car without kilometres or a flat without BHK looks incomplete and gets skipped.',
                            'Fill what you know honestly. Guessy numbers destroy trust at meetup. If a field does not apply, use the closest accurate option or describe the nuance in the description.',
                        ],
                    ],
                    [
                        'q' => 'How many photos should I upload? Any tips?',
                        'a' => [
                            'Use as many clear photos as the flow allows. Cover all angles, labels, accessories, screens, scratches and the odometer/serial where relevant. First photo is the cover — reorder so the strongest image leads.',
                            'Natural daylight beats yellow indoor bulbs. Avoid heavy beauty filters and stock photos. For phones, show screen on + edges + box/bill if you have them. For furniture, show scale (a person or doorway helps).',
                        ],
                        'bullets' => [
                            'Daylight > indoor yellow light',
                            'Show defects on purpose — buyers notice anyway',
                            'Reorder so cover is sharp and honest',
                            'Compression runs before upload to save data',
                        ],
                    ],
                    [
                        'q' => 'How should I write the title and price?',
                        'a' => [
                            'Titles work best when they include brand/model + key fact + area vibe (example: “iPhone 12 128GB — battery 86% — Andheri East”). Avoid ALL CAPS and keyword stuffing.',
                            'Price in ₹ should be realistic. “₹1” or “Message for price” usually performs poorly and can be moderated. Mark negotiable if you mean it. After a price drop, consider a bump so people who scrolled past see the update.',
                        ],
                    ],
                    [
                        'q' => 'Why is my ad Pending or Rejected?',
                        'a' => [
                            'New or edited ads can enter a moderation queue. Rejection includes a reason: unclear photos, prohibited item, misleading price, wrong category, duplicate spam, etc.',
                            'Edit and resubmit. If you disagree, contact support with the listing ID and a calm explanation. Promoting a rejected/pending ad is usually blocked until the listing is Active again.',
                        ],
                    ],
                    [
                        'q' => 'Can I edit an ad after publishing?',
                        'a' => [
                            'Yes — My Ads → Edit. You can update photos, price, description and many fields. Significant edits may re-enter moderation.',
                            'If the item sold, Mark sold instead of ghosting chatters. If you only need more visibility, improve the listing first; then consider Bump/Featured.',
                        ],
                    ],
                    [
                        'q' => 'How long do ads stay Active?',
                        'a' => [
                            'Default expiry is configurable (commonly around 30 days). You get an expiry reminder notification when reminders are enabled.',
                            'From My Ads you can renew, repost, edit, mark sold or delete. Leaving sold inventory Active frustrates buyers and wastes everyone’s time.',
                        ],
                    ],
                    [
                        'q' => 'Can I run multiple ads at once?',
                        'a' => [
                            'Yes, within your free monthly ad limit. Side-hustles that outgrow the free allotment can buy extra-ad packs. Limits exist to reduce spam floods on Home.',
                            'Near-duplicate spam ads can trigger moderation even if you are under the numeric limit. Creating extra accounts to bypass limits violates the Terms.',
                        ],
                    ],
                ],
            ],

            'My Ads & insights' => [
                'intro' => 'After you publish: statuses, renewals, insights, and what to do when views are high but chats are low.',
                'items' => [
                    [
                        'q' => 'What do the My Ads tabs mean?',
                        'a' => [
                            'Common tabs: Active (live in search), Pending (awaiting moderation), Sold (you marked complete), Expired (past end date), Rejected (needs fixes). Exact labels follow the app version.',
                            'Actions usually include edit, delete, mark sold, renew, repost and promote. Check insights on each listing before spending on a boost.',
                        ],
                    ],
                    [
                        'q' => 'What are ad insights?',
                        'a' => [
                            'Per listing you can see views, chats started, and favourites. They tell you whether the problem is discovery or conversion.',
                            'High views + low chats often means weak title, unclear photos, or uncompetitive price. Low views may mean the category/location is quiet — or that Featured/Top could help after the listing itself is strong.',
                        ],
                        'bullets' => [
                            'Low views → photos/category/boost',
                            'High views, low chats → price/title/trust',
                            'Chats but no meetup → replies too slow / unclear terms',
                        ],
                    ],
                    [
                        'q' => 'What if I sold the item outside Tan Tan?',
                        'a' => [
                            'Mark the ad Sold (or delete it) so you stop receiving chats. Reply once to open threads that the item is gone — then mark sold.',
                            'Leaving sold inventory Active hurts your reputation signals over time and trains buyers to distrust “available” labels on your profile.',
                        ],
                    ],
                    [
                        'q' => 'Can I repost the same item after expiry?',
                        'a' => [
                            'Yes via renew/repost from My Ads when available. Refresh photos if the item changed condition. Do not create five near-identical Active ads for the same single item — that looks like spam.',
                        ],
                    ],
                ],
            ],

            'Buying & search' => [
                'intro' => 'Location, filters, favourites, saved searches and the checklist before you travel across town.',
                'items' => [
                    [
                        'q' => 'How do I find items near me?',
                        'a' => [
                            'Set location via GPS or manual city/area search. Home shows fresh recommendations and Featured near you. Open Search and Filters for radius, price range, condition and category-specific fields.',
                            'Saved searches reopen complex filters in one tap (example: “Activa under ₹50,000 within 15 km”). Combine with notifications where enabled so new matches do not disappear into the feed.',
                        ],
                    ],
                    [
                        'q' => 'What should I check before meeting a seller?',
                        'a' => [
                            'Open the full gallery and zoom. Read the description and details grid. Check map distance and whether the area matches the price. Open the seller profile: badge, ratings, other listings, member-since.',
                            'Chat history should show consistent answers. Meet in public daylight. Inspect before paying. Read the Safety tips page for category-specific scam patterns (phones, vehicles, rentals, jobs).',
                        ],
                        'bullets' => [
                            'Gallery + defects',
                            'Details vs photos consistency',
                            'Seller profile & ratings',
                            'Public meetup, inspect first',
                        ],
                    ],
                    [
                        'q' => 'Can I save favourites and searches?',
                        'a' => [
                            'Yes. Favourites bookmark listings you are considering. Recent and saved searches remember filters so you are not rebuilding them every evening.',
                            'Favourites are not a hold on the item — popular phones and scooters move fast. Message early if you are serious, and still verify at meetup.',
                        ],
                    ],
                    [
                        'q' => 'Who pays for transport or delivery?',
                        'a' => [
                            'Tan Tan does not run a courier for user-to-user goods. Agree in chat who travels, who arranges a tempo, and whether the price is “as is, pickup only”.',
                            'Put the agreement in chat so both sides remember. Be wary of sellers who insist on advance courier payment to a personal UPI before you have seen the item.',
                        ],
                    ],
                    [
                        'q' => 'What if the item looks different at meetup?',
                        'a' => [
                            'You are not obliged to buy. Walk away from pressure or bait-and-switch. If the listing was clearly misleading, report the ad with meetup photos so moderators can review.',
                            'For minor wear that was disclosed in photos, renegotiate politely or leave — do not threaten fake police cases over normal second-hand condition.',
                        ],
                    ],
                    [
                        'q' => 'Can I ask for a video call before visiting?',
                        'a' => [
                            'Yes — many serious sellers will show the item live (screen on, engine start, room walkthrough). Ask politely in Tan Tan chat.',
                            'Refusal alone is not always a scam (some people are busy or camera-shy). Refusal plus pressure to pay an advance is a strong red flag.',
                        ],
                    ],
                ],
            ],

            'Chat & offers' => [
                'intro' => 'Realtime messaging, Make Offer, phone visibility, WhatsApp moves, and how to keep a clean record if something goes wrong.',
                'items' => [
                    [
                        'q' => 'Is chat realtime?',
                        'a' => [
                            'Yes. Threads support text, images, unread counts and typing indicators. Offers appear as structured chips so price negotiation stays readable.',
                            'Keep early negotiation in Tan Tan when possible. That history helps if you later report abuse. Push notifications alert you to new messages when enabled.',
                        ],
                    ],
                    [
                        'q' => 'What is Make Offer?',
                        'a' => [
                            'A structured way to propose a price inside chat. Sellers can accept, decline or counter without losing the message timeline.',
                            'It reduces endless “last price?” spam and creates a clearer record of what was agreed if a dispute about the number appears later. It does not make Tan Tan a party to the sale or a payment guarantor.',
                        ],
                    ],
                    [
                        'q' => 'Why can’t I see the seller’s phone number?',
                        'a' => [
                            'Numbers are masked by default for privacy and spam control. You can always chat inside Tan Tan without revealing phones.',
                            'If a seller enables Show phone, Call and WhatsApp actions appear on the listing sticky bar. Sellers: enabling this is a privacy trade-off — expect more calls, including some spam.',
                        ],
                    ],
                    [
                        'q' => 'Someone asked me to move to WhatsApp immediately',
                        'a' => [
                            'Common pattern. Prefer keeping early negotiation in Tan Tan chat. If you eventually move, still never pay advances to strangers and still meet in public.',
                            'Report users who only want to phish OTPs, send fake payment screenshots, or push “job registration fees”. Official Tan Tan staff will not ask for your OTP on WhatsApp.',
                        ],
                    ],
                    [
                        'q' => 'Can I send images in chat?',
                        'a' => [
                            'Yes — useful for extra angles, bill photos, or showing a defect. Do not send illegal content. Do not upload photos of your Aadhaar or full card numbers.',
                            'If someone sends malware-looking links or QR codes asking you to “confirm payment”, stop and report.',
                        ],
                    ],
                    [
                        'q' => 'How do I mute or leave a chat?',
                        'a' => [
                            'Use block to stop further messages from a user. You can still report them separately. Exact mute/archive controls follow the app version under the chat thread menu.',
                            'Blocking is personal — it does not automatically ban them platform-wide. For policy violations, report with evidence.',
                        ],
                    ],
                ],
            ],

            'Trust & safety' => [
                'intro' => 'Reports, blocks, scam patterns, and what Tan Tan can (and cannot) do after an offline problem. Read the full Safety tips page for deeper checklists.',
                'items' => [
                    [
                        'q' => 'How do I report a bad ad or user?',
                        'a' => [
                            'From listing detail or chat, open Report and choose a reason. Moderators review the queue. Repeated reports can auto-hide a listing pending review.',
                            'When emailing support, include listing ID, registered phone, screenshots and a short timeline. Calm, specific reports are faster to action than insults.',
                        ],
                    ],
                    [
                        'q' => 'What does Block do?',
                        'a' => [
                            'Blocked users cannot message you further. Blocking is per-account and personal. It does not remove their ads for everyone else.',
                            'If they violated policies (scam, harassment, prohibited items), report as well so moderators can review beyond your thread.',
                        ],
                    ],
                    [
                        'q' => 'I think I was scammed offline — what now?',
                        'a' => [
                            'If money or goods were stolen, contact local police and keep UPI references, chat screenshots and listing URLs. Report the user/listing in-app with that evidence.',
                            'Tan Tan can investigate account abuse and remove bad actors. We cannot reverse cash or UPI you sent directly to another person — we are not the merchant of record for user goods. Do not send more money hoping to “unlock” a refund.',
                        ],
                    ],
                    [
                        'q' => 'What are common scam red flags?',
                        'a' => [
                            'Luxury items at absurd prices, stock photos, “urgent abroad / Army transfer” stories, pressure to pay advance before viewing, fake payment screenshots, requests for OTP, job offers that charge a registration fee, and rental deposits before you see the property.',
                            'Slow down. Video call. Meet publicly. Inspect first. Read Safety tips for phones, vehicles, property and jobs specifically.',
                        ],
                        'bullets' => [
                            'No advance for strangers',
                            'No OTP sharing ever',
                            'Inspect before pay',
                            'Job fees = walk away',
                        ],
                    ],
                    [
                        'q' => 'Does Tan Tan guarantee deals?',
                        'a' => [
                            'No. Tan Tan provides marketplace software, moderation tools and optional boosts. Offline inspection and payment choices are yours.',
                            'Verified badges and ratings reduce risk but never replace common sense. Follow Safety tips every time — especially for high-ticket vehicles and property.',
                        ],
                    ],
                ],
            ],

            'Payments & packages' => [
                'intro' => 'Bump, Featured, Top Ad, invoices, refunds and the difference between paying Tan Tan for visibility vs paying a seller for goods.',
                'items' => [
                    [
                        'q' => 'Do I pay Tan Tan when I buy a phone from someone?',
                        'a' => [
                            'No. Payment for goods is between you and the other person (cash/UPI offline). Tan Tan is not the merchant of record for that sale and does not escrow it by default.',
                            'Separate optional payments: if you buy a Bump/Featured/Top package, you pay Tan Tan via Razorpay for visibility — not for the underlying item.',
                        ],
                    ],
                    [
                        'q' => 'What do packages cost?',
                        'a' => [
                            'Indicative website prices: Bump ₹49, Featured ₹149 for 7 days, Top Ad ₹299 for 14 days. Seasonal bundles may appear as in-app banners.',
                            'Always confirm the price on the Packages screen before paying — the in-app checkout amount is authoritative if this website differs. Prices can change.',
                        ],
                    ],
                    [
                        'q' => 'Bump vs Featured vs Top Ad — which should I buy?',
                        'a' => [
                            'Bump is a short visibility refresh — useful right after a price drop or new photos. Featured (often best value) keeps you in Featured surfaces for about a week. Top Ad is longer priority for higher-ticket inventory.',
                            'Never boost a weak listing first. Fix photos, category fields and price, then promote. Insights tell you whether you need discovery (boost) or conversion (listing quality).',
                        ],
                        'bullets' => [
                            'Bump — quick refresh',
                            'Featured — 7-day visibility (common best value)',
                            'Top Ad — longer priority for big tickets',
                            'Extra ad packs — more Active slots',
                        ],
                    ],
                    [
                        'q' => 'Which payment methods work?',
                        'a' => [
                            'Razorpay-supported methods such as UPI and cards, subject to availability on your device and bank. Tan Tan does not store full card PAN/CVV.',
                            'Failed payments do not activate boosts. Check your UPI app, retry, or try another method. Keep the payment ID for support.',
                        ],
                    ],
                    [
                        'q' => 'Where are invoices?',
                        'a' => [
                            'Profile → Packages / Payments → transaction history. PDF invoices are available for successful package purchases.',
                            'Download invoices before you delete your account if you need them for records. Package purchases are tied to your account and are non-transferable.',
                        ],
                    ],
                    [
                        'q' => 'Can I get a refund on a boost?',
                        'a' => [
                            'If payment succeeded but the promotion failed to activate due to a technical error on Tan Tan’s side, contact support with the payment ID for re-apply or refund at our reasonable discretion.',
                            'Time already consumed on a live promotion is generally non-refundable. Packages are non-transferable. See Terms for full billing language.',
                        ],
                    ],
                    [
                        'q' => 'Does a boost guarantee a sale?',
                        'a' => [
                            'No. Boosts improve relative visibility on relevant surfaces. Photos, honest price, complete fields and fast replies still decide whether chats convert to meetups.',
                            'If anyone promises “guaranteed sale with this package”, that claim is false — including if it appears in a random WhatsApp forward.',
                        ],
                    ],
                    [
                        'q' => 'Can I boost a Pending or Rejected ad?',
                        'a' => [
                            'Usually no — wait until the listing is Active. If money was captured and activation cannot complete because the ad was rejected, contact support with the payment ID for the applicable remedy under Terms.',
                        ],
                    ],
                ],
            ],

            'Categories deep-dive' => [
                'intro' => 'Short answers buyers and sellers ask about vehicles, property, phones, jobs and services. Full field lists live on the Categories page.',
                'items' => [
                    [
                        'q' => 'Any special tips for selling or buying vehicles?',
                        'a' => [
                            'Sellers: daylight photos of all sides, odometer, tyre condition, service notes, honest kilometres and owners. Buyers: ask for RC, service history, cold start video; meet at busy lots or service centres; never transfer full money before seeing the vehicle.',
                            'Match chassis/engine numbers carefully on higher-value deals. “Urgent army transfer abroad” + underpriced luxury car is a classic scam pattern.',
                        ],
                    ],
                    [
                        'q' => 'How should I handle property / PG listings?',
                        'a' => [
                            'Location honesty matters. Misleading pins and bait rents get moderated. Visit in person. Verify who collects deposit/rent in writing. Tan Tan does not collect rent on your behalf.',
                            'Never pay a “listing unlock fee” or share OTPs for fake owner-verification links. PG deposits before viewing are a common red flag.',
                        ],
                    ],
                    [
                        'q' => 'What should I verify on used phones?',
                        'a' => [
                            'IMEI, activation/iCloud/Google lock, battery health notes, screen burn, camera/mic/call test at meetup. Prefer UPI that reflects instantly over screenshot “proof”.',
                            'Sellers: show screen-on photos, edges, box/bill if available. Factory reset only after payment clears. Buyers: walk away from sealed “brand new” phones at absurd discounts from random profiles.',
                        ],
                    ],
                    [
                        'q' => 'Are job posts allowed? What about registration fees?',
                        'a' => [
                            'Local jobs and gigs can be listed with role, salary range and work mode fields. Tan Tan is not a payroll provider.',
                            'Never pay for a job offer. “Registration fee”, “training kit fee” or “HR verification fee” demanded before interview is a scam signal — report it.',
                        ],
                    ],
                    [
                        'q' => 'Can I list services like tutoring or repairs?',
                        'a' => [
                            'Yes — Services category with area coverage and pricing model. Agree scope and price in Tan Tan chat before work starts. Prefer staged payments tied to milestones for larger jobs.',
                            'Photos of past work help, but still verify in person for high-value electrical/plumbing work. Be clear about call-out charges in the description.',
                        ],
                    ],
                    [
                        'q' => 'What about pets?',
                        'a' => [
                            'Only where legally allowed. Share vaccination and care details honestly. Follow animal welfare rules. Report illegal wildlife trade immediately.',
                            'Avoid impulse purchases. Ask for vet records. Meet in safe ventilated spaces and never transport animals unsafely “to save time”.',
                        ],
                    ],
                ],
            ],

            'Privacy & deletion' => [
                'intro' => 'What we store, phone masking, location controls, data export and permanent account deletion (store-compliant).',
                'items' => [
                    [
                        'q' => 'What data do you store?',
                        'a' => [
                            'Account details, listings, chat content needed to deliver the service, approximate location you choose, device tokens for push, payment identifiers for packages, and safety signals (reports/blocks).',
                            'We do not store full card numbers on Tan Tan servers. We do not sell personal data to data brokers. Details and legal bases are in the Privacy Policy.',
                        ],
                    ],
                    [
                        'q' => 'Do you sell my data?',
                        'a' => [
                            'No. We do not sell personal data to data brokers. We share limited data with infrastructure, OTP, push and payment processors as needed to run the Service under contracts — see Privacy Policy § Sharing.',
                        ],
                    ],
                    [
                        'q' => 'How do I delete my account?',
                        'a' => [
                            'In-app (recommended): Profile → Settings → Delete account → read the warning → confirm with OTP. Or email support@tantan.app / use the Delete Account web page if you cannot open the app.',
                            'Deletion is permanent. Export invoice PDFs first. Primary personal stores are removed/anonymised promptly (target within ~30 days), except legal/tax/fraud records described in the Privacy Policy.',
                        ],
                        'bullets' => [
                            'In-app OTP confirm is fastest',
                            'Email fallback if you lost access',
                            'Invoices: download before delete',
                        ],
                    ],
                    [
                        'q' => 'Will my chats disappear after deletion?',
                        'a' => [
                            'Your access is removed. Other users may still see messages they already received. Some records may be retained for legal, tax or fraud prevention as described in the Privacy Policy.',
                        ],
                    ],
                    [
                        'q' => 'Can I download a copy of my data?',
                        'a' => [
                            'Email privacy@tantan.app with your registered phone/email. We will guide you through available export options under applicable Indian law and product capabilities at the time of request. Identity verification is required.',
                        ],
                    ],
                    [
                        'q' => 'How do I turn off location or Show phone?',
                        'a' => [
                            'Revoke location in the phone’s Settings for Tan Tan, or switch to a manual city in-app. Nearby ranking quality drops without a good location, but browsing still works.',
                            'Show phone is a seller privacy toggle. Leave it off to keep Call/WhatsApp hidden; buyers can still chat inside Tan Tan.',
                        ],
                    ],
                ],
            ],

            'Moderation & limits' => [
                'intro' => 'Why free limits exist, what auto-hide means, dealer use, bans and how to appeal without making things worse.',
                'items' => [
                    [
                        'q' => 'Why do free ad limits exist?',
                        'a' => [
                            'Limits reduce spam floods and keep Home useful for neighbours. Genuine side-hustles can buy extra-ad packs instead of creating duplicate accounts.',
                            'Duplicate accounts to evade limits or bans violate the Terms and often extend restrictions when discovered.',
                        ],
                    ],
                    [
                        'q' => 'What triggers automatic hide?',
                        'a' => [
                            'Repeated user reports, clear policy signals, or admin rules can hide a listing pending review. You will see status changes under My Ads.',
                            'Edit and resubmit when asked. Appeal via support with the listing ID if you believe it was a mistake — include context, not only anger.',
                        ],
                    ],
                    [
                        'q' => 'Are dealers allowed?',
                        'a' => [
                            'Local dealers who follow listing quality, disclosure and legal rules may use Tan Tan subject to limits and policies. Mass scraping, fake locations, bait ads and account farms are not allowed.',
                            'Be honest in the listing text when you are a business. Consumers deserve to know who they are meeting.',
                        ],
                    ],
                    [
                        'q' => 'Can I appeal a ban or restriction?',
                        'a' => [
                            'Email support@tantan.app from a reachable address. Include the phone number on the account and calm evidence. Creating a new account to evade a ban usually makes the situation worse.',
                            'We review appeals in queue order. Safety and fraud cases take priority over “I want my ads back today” without context.',
                        ],
                    ],
                ],
            ],

            'App, notifications & devices' => [
                'intro' => 'Permissions, battery/data, switching phones, languages, and India-first availability.',
                'items' => [
                    [
                        'q' => 'Why should I allow notifications?',
                        'a' => [
                            'Chat messages, offer updates, ad approval/rejection, expiry reminders and (optionally) saved-search alerts are much easier with push enabled.',
                            'You can disable categories later in Settings if you only want chat alerts. Denying all notifications means you may miss buyers while you are away from the app.',
                        ],
                    ],
                    [
                        'q' => 'I changed my phone — how do I keep my ads?',
                        'a' => [
                            'Sign in with the same OTP phone / Google account. Listings, chats and favourites follow the account, not the device hardware.',
                            'Re-allow notifications and location on the new phone. Old device tokens stop receiving push after you stop using them.',
                        ],
                    ],
                    [
                        'q' => 'Battery or data usage feels high',
                        'a' => [
                            'Realtime chat and image uploads use data. Prefer Wi‑Fi for large photo sets. Lower camera resolution before picking photos on a slow connection if needed.',
                            'If the app misbehaves after an OS update, force-stop, clear cache (Android), or reinstall — then contact support with your phone model and OS version.',
                        ],
                    ],
                    [
                        'q' => 'Is Tan Tan available outside India?',
                        'a' => [
                            'The product is India-first: ₹ pricing, +91 OTP, local categories, Razorpay rails. Cross-border shipping and foreign numbers are outside the current design.',
                            'If you browse from abroad, you do so on your own initiative; policies still assume Indian neighbourhood trade.',
                        ],
                    ],
                    [
                        'q' => 'OTP not arriving — what should I try?',
                        'a' => [
                            'Check network signal, wait for the timer before resending, confirm +91 and the 10-digit number, and ensure SMS is not blocked by the carrier or a third-party SMS blocker.',
                            'Do not keep hammering resend — rate limits protect everyone. Try again later or contact support if the number is correct and hours have passed.',
                        ],
                    ],
                    [
                        'q' => 'The app crashes on upload — help?',
                        'a' => [
                            'Reduce photo count/size, switch to Wi‑Fi, free some device storage, and update to the latest app version. Very large uncompressed bursts can fail on low memory devices.',
                            'If it still crashes, email support with device model, OS version, and roughly which step fails (photos vs details vs review).',
                        ],
                    ],
                ],
            ],

            'Troubleshooting & support' => [
                'intro' => 'Fast fixes and how to contact us so a human can actually help on the first reply.',
                'items' => [
                    [
                        'q' => 'What should I include when contacting support?',
                        'a' => [
                            'Registered +91 phone, email, city, listing ID, payment ID (for package issues), screenshots, and a short timeline of what you did vs what you expected.',
                            'Vague “app not working” messages take longer. Safety reports are prioritised. Hours: Mon–Sat 10:00–19:00 IST via support@tantan.app or the Contact form.',
                        ],
                        'bullets' => [
                            'support@tantan.app — product help',
                            'privacy@tantan.app — data requests',
                            'legal@tantan.app — legal notices',
                            'hello@tantan.app — partners / press',
                        ],
                    ],
                    [
                        'q' => 'In-app Help vs email vs this Contact form?',
                        'a' => [
                            'In-app Help is best when you are signed in — it keeps account context. Email and the website Contact form work when you cannot open the app or are on desktop.',
                            'Partnerships and press should use hello@tantan.app. Do not use support channels to negotiate someone else’s listing price.',
                        ],
                    ],
                    [
                        'q' => 'Will support mediate my offline deal?',
                        'a' => [
                            'Support reviews policy abuse, account access, moderation mistakes and package activation failures. We do not haggle prices or referee every footpath disagreement.',
                            'For crimes, contact police first, then report in-app with evidence so we can act on the account.',
                        ],
                    ],
                    [
                        'q' => 'Where else should I read on this website?',
                        'a' => [
                            'How it works (full product walkthrough), Buyer’s guide, Seller’s playbook, Safety tips, Categories, Packages, Terms, Privacy and Delete account.',
                            'Those pages go deeper than FAQ chips. FAQ is for targeted questions; guides are for learning the whole loop.',
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function answerCount(): int
    {
        $n = 0;
        foreach (self::groups() as $group) {
            $n += count($group['items']);
        }

        return $n;
    }
}
