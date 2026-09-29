# Legal pages

Public documents are in `lang/{pl,uk,en}/legal.php`. Routes and translated
canonical URLs are defined by `LegalPageController`. Change the date in the
translations and the semantic time element when substantively revising content.

## Verified Business Details

Read from the restaurant's public GoOrder configuration on 2026-09-29:
[GoOrder configuration](https://umamisushifood.goorder.pl/api/config).

- Legal name: DARIA JANZ, MARYNA ZASLAVSKA SPÓŁKA CYWILNA.
- NIP: 9562405793.
- Business address: gen. Karola Kniaziewicza 52A/3, 87-100 Toruń.
- Restaurant / collection: Gen. Andersa 72, Toruń.
- Contact email: not supplied by the public configuration. Do not invent one.
- Existing restaurant phone: +48 513 233 722.

The GoPOS organization endpoint identifies the venue and company ID, but the
public GoOrder configuration supplies the actual legal name, tax ID and address.

## Review Still Needed

These texts describe the implemented service; they are not a legal compliance
certification. The operator should have a Polish legal adviser review the final
documents, provide a customer contact email, approve precise retention periods,
and verify processor agreements and international transfer safeguards. No
unverified automatic deletion periods or legal guarantees are promised.

Reference sources checked during drafting:

- [GDPR, including Articles 6 and 13](https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32016R0679).
- [UODO guidance on withdrawing consent](https://uodo.gov.pl/pl/493/2255).
- [Polish Consumer Rights Act, including Articles 7a and 38](https://eli.gov.pl/api/acts/DU/2024/1796/text.html).
- [UOKiK: non-conformity and complaints](https://prawakonsumenta.uokik.gov.pl/reklamacja/niezgodnosc/).
- [Google Analytics cookie lifetimes](https://support.google.com/analytics/answer/11397207?hl=en-GB).

## Privacy Controls

`privacy.js` is loaded independently of the menu/checkout script. Analytics is
loaded only after acceptance, with advertising consent denied. Withdrawal
disables analytics, removes accessible first-party GA cookies and reloads an
already-tracked page. It does not erase data previously held by Google.

The map uses a separate per-page explicit activation and never a live iframe
source before activation. Order tracking pages continue to suppress analytics.
Checkout integration flags and business data are not changed by this work.

Run `php artisan test` and `node --test tests/Browser/privacy.test.cjs`.
