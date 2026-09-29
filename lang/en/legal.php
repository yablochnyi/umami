<?php

return [
    'updated_label' => 'Last updated:',
    'updated' => '29 September 2026',
    'contents' => 'Contents',
    'related' => 'Related documents',
    'cookie_settings' => 'Cookie settings',
    'map_load' => 'Show Google map',
    'map_notice' => 'Enabling the map sends your IP address and browser data to Google. The map may use cookies under Google’s policy.',
    'privacy' => [
        'title' => 'Privacy policy',
        'description' => 'How we use data when you browse the menu, place an order or contact Umami Sushi & Food in Toruń.',
        'sections' => [
            ['heading' => '1. Contact about your data', 'body' => [
                'The data controllers are the partners trading as DARIA JANZ, MARYNA ZASLAVSKA SPÓŁKA CYWILNA, tax identification number (NIP) 9562405793, business address: ul. gen. Karola Kniaziewicza 52A/3, 87-100 Toruń, Poland. The Umami Sushi & Food restaurant is at ul. Gen. Andersa 72, 87-100 Toruń.',
                'For data and order enquiries, call the restaurant on +48 513 233 722 or write to the business address above.',
            ]],
            ['heading' => '2. Data we process', 'body' => [
                'For orders we store your name, phone, email, ordered items, quantities, prices, payment and collection method, requested time and order status. Delivery requires an address; invoices also require a tax identification number (NIP). We store comments entered in the form. Do not include unnecessary sensitive information.',
                'The server may record your IP address, request time, visited page and browser information for diagnostics. The form does not collect card numbers or CVV codes. Sales statistics include the source, amounts and order items; the separate reporting database does not copy customer contact details.',
            ]],
            ['heading' => '3. Purposes and legal grounds', 'body' => [
                'Processing orders, delivery and related contact are necessary to perform a contract or take steps before entering one (GDPR Article 6(1)(b)). Tax and accounting records are processed to meet legal obligations (point c). Security, handling claims and internal sales analysis serve the controller’s legitimate interests (point f).',
                'Optional Google Analytics visitor statistics run only with consent (point a). Analytics consent is not required to buy from us. We do not make solely automated decisions about customers that produce legal effects.',
            ]],
            ['heading' => '4. Service providers and data transfers', 'body' => [
                'Authorised staff, hosting and technical service providers, GoPOS and GoOrder where used for the order, delivery providers and accounting support may access data as necessary. Competent authorities may also receive data.',
                'To calculate delivery, the town, street and building number may be sent to Nominatim (OpenStreetMap) to determine coordinates. This request does not include your name, email, phone or apartment number; results are cached for one month.',
                'Google receives technical data after analytics consent or when you explicitly enable the map. Google services may involve processing outside the EEA. For processing locations and transfer safeguards, see https://policies.google.com/privacy. Visiting Wolt, Pyszne or social networks is also subject to the chosen service’s policies.',
            ]],
            ['heading' => '5. Retention', 'body' => [
                'Retention depends on the purpose: completing an order, handling complaints, mandatory accounting retention periods and limitation periods for claims. Not all data needs the same retention period. Completing an order does not automatically delete its history.',
                'Technical log retention depends on hosting configuration; analytics event retention depends on Google Analytics settings. Contact the restaurant for the period applicable to your data. Browser storage is described in the cookie policy.',
            ]],
            ['heading' => '6. Your rights and voluntary provision', 'body' => [
                'Under the GDPR you can request access, a copy, rectification, erasure or restriction, and where applicable portability, and object to processing based on legitimate interests. Legal obligations may limit erasure. We may request information needed to verify your identity.',
                'Withdraw analytics consent through “Cookie settings” in the footer. Withdrawal does not affect the lawfulness of earlier processing. You may complain to Poland’s supervisory authority, UODO: https://uodo.gov.pl. Providing order data is voluntary, but we cannot fulfil an order without the required information. You can browse the menu without ordering or consenting to analytics.',
            ]],
        ],
    ],
    'cookies' => [
        'title' => 'Cookie policy',
        'description' => 'What we store in your browser, why we use it and how to change your choice.',
        'sections' => [
            ['heading' => '1. Cookies and browser storage', 'body' => [
                'Cookies are small records stored in your browser by a website. We also use localStorage, which remains after closing the browser, and sessionStorage within a browser tab. Some storage supports orders; analytics is optional.',
            ]],
            ['heading' => '2. Essential storage', 'body' => [
                'The application session cookie and XSRF-TOKEN support sessions, forms and protection against forged requests. Session lifetime follows server configuration and may be renewed as you use the site.',
                'umami_cart stores your basket in localStorage. umami_cookie_consent remembers your choice in localStorage until changed or cleared and in a fallback cookie lasting up to 12 months. These are not advertising tools.',
                'umami_last_order_status stores the latest order link and status; the return link checks validity for 7 days after the latest stored update or estimated ready time, whichever is later. Technical order confirmation markers prevent the basket from being cleared twice. localStorage entries remain until removed or replaced; do not share your private order link.',
            ]],
            ['heading' => '3. Optional Google analytics', 'body' => [
                'If analytics is configured, we load Google Analytics only after you choose “I agree”. It may process browser identifiers, visited pages, events and device data. _ga and _ga_* cookies normally last up to 2 years and may be renewed according to Google settings. A umami_purchase_tracked_* sessionStorage marker prevents duplicate purchase counting in the same tab.',
                'Consent covers visitor statistics, not personalised advertising. Google Analytics does not run on the private order tracking page.',
            ]],
            ['heading' => '4. Changing or withdrawing consent', 'body' => [
                '“Essential only” declines analytics. Both choices keep the menu and ordering available. Open “Cookie settings” in the footer at any time and choose “Essential only” to withdraw consent.',
                'On withdrawal we disable further collection and remove _ga cookies accessible to this site. The change may reload the page, so finish editing any form first. It does not remove data already sent to Google. Clearing all site data in your browser also removes your basket and saved order link. Your choice applies to the current browser.',
            ]],
            ['heading' => '5. Maps and external websites', 'body' => [
                'The Google map does not load automatically. Enable it separately using the button beside the map after reading the data transfer notice. Withdrawing analytics consent does not erase earlier map data; reloading the page blocks the map again.',
                'External links open services with their own cookie rules. Our privacy policy explains personal data processing and how to contact the restaurant.',
            ]],
        ],
    ],
    'terms' => [
        'title' => 'Terms of website use and orders',
        'description' => 'Conditions for browsing the menu, placing orders, collection, delivery and reporting problems.',
        'sections' => [
            ['heading' => '1. Restaurant and contact', 'body' => [
                'The sellers are the partners trading as DARIA JANZ, MARYNA ZASLAVSKA SPÓŁKA CYWILNA, tax identification number (NIP) 9562405793, business address: ul. gen. Karola Kniaziewicza 52A/3, 87-100 Toruń, Poland.',
                'umamisushifood.pl presents the menu and accepts orders for the Umami Sushi & Food restaurant, ul. Gen. Andersa 72, 87-100 Toruń. For orders, changes, complaints or website issues, call +48 513 233 722 or write to the business address. The restaurant and business addresses differ; collect orders at ul. Gen. Andersa 72.',
            ]],
            ['heading' => '2. Menu, prices and allergens', 'body' => [
                'Prices are in Polish złoty (PLN), including taxes. Delivery charges and the total are shown before submission. Dishes, lunches and options may depend on the day, time and stock. Confirmed orders follow the agreed terms, not later price changes.',
                'Photographs illustrate the dishes. Check descriptions for ingredients and allergens, and ask staff before ordering if unsure. An allergen missing from a short description does not establish its absence or exclude cross-contact.',
            ]],
            ['heading' => '3. Placing and confirming orders', 'body' => [
                'Choose dishes and quantities, check your basket, provide contact details and select collection or delivery, time and payment method. Correct errors before pressing the order button. Submitting an order with an obligation to pay expresses your intention to purchase on the displayed terms.',
                'A technical submission or pending message does not yet mean staff have accepted the order. Acceptance and timing are communicated through the order status or by the restaurant. If confirmation is missing, call before submitting again. Changes to prices or items require your agreement.',
            ]],
            ['heading' => '4. Payment, collection and delivery', 'body' => [
                'Available payment methods appear in the form; choosing a card does not charge it through this form. Do not enter card details in comments. Supply your NIP during ordering if you need an invoice.',
                'Delivery is available in supported zones and hours, subject to the minimum order and charge displayed before submission. Collection takes place at the restaurant. Provide a correct address and a phone number where you can be reached to arrange receipt.',
                'Preparation time and the countdown are estimates, not confirmation of physical delivery. Contact the restaurant about delays or difficulties. Times use the time zone in Poland.',
            ]],
            ['heading' => '5. Changes, cancellation and withdrawal', 'body' => [
                'Request changes or cancellation by phone as soon as possible. Whether they can be accommodated depends on preparation progress and agreement with the restaurant.',
                'The statutory 14-day right to withdraw without a reason does not apply to goods liable to deteriorate or expire rapidly, including freshly prepared meals (Article 38(1)(4) of the Polish Consumer Rights Act). This does not remove complaint rights or other statutory consumer protections.',
            ]],
            ['heading' => '6. Complaints', 'body' => [
                'Contact the restaurant if an order is incomplete, differs from what was agreed or has quality issues. Provide the order number or date, a description, your requested remedy and contact details for a response. A photo can help but is not an absolute condition for making a complaint.',
                'You can report issues by phone or in writing to the restaurant address. Statutory consumer complaint rules and deadlines apply, including generally 14 days to respond. You retain access to consumer ombudsman assistance and out-of-court dispute resolution.',
            ]],
            ['heading' => '7. Website use and document versions', 'body' => [
                'Ordering requires internet access, a current browser, JavaScript and essential site storage. Do not submit unlawful content or disrupt the service. Texts and photographs are legally protected, subject to permitted use.',
                'Orders placed directly on external platforms are also governed by the terms shown there. The privacy and cookie policies explain data processing. This document is available in Polish, Ukrainian and English. Updates do not change previously confirmed orders or mandatory consumer rights.',
            ]],
        ],
    ],
];
