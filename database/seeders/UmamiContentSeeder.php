<?php

namespace Database\Seeders;

use App\Models\GalleryImage;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Models\SiteText;
use App\Models\SocialLink;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UmamiContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(<<<'JSON'
{
  "supported": [
    "pl",
    "uk",
    "en"
  ],
  "default_locale": "pl",
  "texts": [
    {
      "key": "title",
      "group": "seo",
      "label": "Meta title",
      "type": "text",
      "sort_order": 1,
      "value": {
        "pl": "Sushi Toruń | Zamów sushi online - Umami Sushi & Food",
        "uk": "Суші Торунь | Купити суші онлайн - Umami Sushi & Food",
        "en": "Sushi Toruń | Order sushi online - Umami Sushi & Food"
      }
    },
    {
      "key": "metaDescription",
      "group": "seo",
      "label": "Meta description",
      "type": "textarea",
      "sort_order": 2,
      "value": {
        "pl": "Zamów sushi w Toruniu: świeże rolki, zestawy sushi, ramen, udon i kuchnia azjatycka. Sushi na wynos przy ul. Gen. Andersa 72.",
        "uk": "Купити суші в Торуні: свіжі роли, суші-сети, рамен, удон та азійська кухня. Суші з собою на вул. Ген. Андерса 72.",
        "en": "Order sushi in Toruń: fresh rolls, sushi sets, ramen, udon and Asian cuisine. Sushi takeaway at Gen. Andersa 72."
      }
    },
    {
      "key": "navBestsellers",
      "group": "navigation",
      "label": "Navigation: bestsellers",
      "type": "text",
      "sort_order": 3,
      "value": {
        "pl": "Bestsellery",
        "uk": "Хіти",
        "en": "Bestsellers"
      }
    },
    {
      "key": "navMenu",
      "group": "navigation",
      "label": "Navigation: menu",
      "type": "text",
      "sort_order": 4,
      "value": {
        "pl": "Menu",
        "uk": "Меню",
        "en": "Menu"
      }
    },
    {
      "key": "navAbout",
      "group": "navigation",
      "label": "Navigation: about",
      "type": "text",
      "sort_order": 5,
      "value": {
        "pl": "O nas",
        "uk": "Про нас",
        "en": "About"
      }
    },
    {
      "key": "navGallery",
      "group": "navigation",
      "label": "Navigation: gallery",
      "type": "text",
      "sort_order": 6,
      "value": {
        "pl": "Galeria",
        "uk": "Галерея",
        "en": "Gallery"
      }
    },
    {
      "key": "navContact",
      "group": "navigation",
      "label": "Navigation: contact",
      "type": "text",
      "sort_order": 7,
      "value": {
        "pl": "Kontakt",
        "uk": "Контакти",
        "en": "Contact"
      }
    },
    {
      "key": "heroEyebrow",
      "group": "hero",
      "label": "Hero eyebrow",
      "type": "text",
      "sort_order": 8,
      "value": {
        "pl": "Toruń, ul. Gen. Andersa 72",
        "uk": "Торунь, вул. Ген. Андерса 72",
        "en": "Toruń, Gen. Andersa 72"
      }
    },
    {
      "key": "heroText",
      "group": "hero",
      "label": "Hero text",
      "type": "textarea",
      "sort_order": 9,
      "value": {
        "pl": "Zamów świeże sushi w Toruniu albo wpadnij na ramen, udon i autorskie zestawy. Umami Sushi & Food przygotowuje rolki i dania azjatyckie na miejscu, z dbałością o smak, ryż i każdy detal.",
        "uk": "Замовте свіже суші в Торуні або завітайте на рамен, удон та авторські сети. В Umami Sushi & Food роли й азійські страви готують на місці з увагою до смаку, рису та кожної деталі.",
        "en": "Order fresh sushi in Toruń or drop in for ramen, udon and signature sets. Umami Sushi & Food prepares rolls and Asian dishes on site, with care for flavor, rice and every detail."
      }
    },
    {
      "key": "heroMenu",
      "group": "hero",
      "label": "Hero menu button",
      "type": "text",
      "sort_order": 10,
      "value": {
        "pl": "Zobacz menu",
        "uk": "Переглянути меню",
        "en": "View menu"
      }
    },
    {
      "key": "heroOrder",
      "group": "hero",
      "label": "Hero order button",
      "type": "text",
      "sort_order": 11,
      "value": {
        "pl": "Zamów online",
        "uk": "Замовити онлайн",
        "en": "Order online"
      }
    },
    {
      "key": "bestsellersTitle",
      "group": "home",
      "label": "Bestsellers title",
      "type": "text",
      "sort_order": 12,
      "value": {
        "pl": "Najczęściej wybierane",
        "uk": "Найчастіше обирають",
        "en": "Most popular"
      }
    },
    {
      "key": "menuTitle",
      "group": "home",
      "label": "Menu title",
      "type": "text",
      "sort_order": 13,
      "value": {
        "pl": "Menu sushi, ramen i kuchni azjatyckiej",
        "uk": "Меню суші, рамену та азійської кухні",
        "en": "Sushi, ramen and Asian menu"
      }
    },
    {
      "key": "aboutEyebrow",
      "group": "about",
      "label": "About eyebrow",
      "type": "text",
      "sort_order": 14,
      "value": {
        "pl": "O nas",
        "uk": "Про нас",
        "en": "About us"
      }
    },
    {
      "key": "aboutTitle",
      "group": "about",
      "label": "About title",
      "type": "text",
      "sort_order": 15,
      "value": {
        "pl": "Sushi w Toruniu z japońską precyzją i autorskim smakiem",
        "uk": "Суші в Торуні з японською точністю та авторським смаком",
        "en": "Sushi in Toruń with Japanese precision and signature flavor"
      }
    },
    {
      "key": "aboutText",
      "group": "about",
      "label": "About text",
      "type": "textarea",
      "sort_order": 16,
      "value": {
        "pl": "Umami Sushi & Food to restauracja sushi w Toruniu przy ul. Gen. Andersa 72, tworzona z pasji do autentycznych smaków Azji i nowoczesnej kuchni fusion. Przygotowujemy świeże sushi, zestawy rolek, ramen, udon, zupy i dania azjatyckie na miejscu, z selekcjonowanych składników. To dobre miejsce na lunch, kolację, spotkanie z przyjaciółmi albo sushi na wynos z odbiorem w restauracji.",
        "uk": "Umami Sushi & Food — ресторан суші в Торуні на вул. Ген. Андерса 72, створений з любові до автентичних смаків Азії та сучасної fusion-кухні. Ми готуємо свіже суші, роли, суші-сети, рамен, удон, супи та азійські страви на місці з ретельно відібраних інгредієнтів. Це затишне місце для обіду, вечері, зустрічі з друзями або суші з собою.",
        "en": "Umami Sushi & Food is a sushi restaurant in Toruń at Gen. Andersa 72, built around authentic Asian flavors and modern fusion cuisine. We prepare fresh sushi, roll sets, ramen, udon, soups and Asian dishes on site from carefully selected ingredients. It is a comfortable place for lunch, dinner, meeting friends or ordering sushi to take away."
      }
    },
    {
      "key": "galleryTitle",
      "group": "gallery",
      "label": "Gallery title",
      "type": "text",
      "sort_order": 17,
      "value": {
        "pl": "Galeria",
        "uk": "Галерея",
        "en": "Gallery"
      }
    },
    {
      "key": "contactTitle",
      "group": "contact",
      "label": "Contact title",
      "type": "text",
      "sort_order": 18,
      "value": {
        "pl": "Kontakt i lokalizacja Umami Sushi Toruń",
        "uk": "Контакти та локація Umami Sushi Торунь",
        "en": "Contact and location of Umami Sushi Toruń"
      }
    },
    {
      "key": "restaurantTitle",
      "group": "contact",
      "label": "Restaurant panel title",
      "type": "text",
      "sort_order": 19,
      "value": {
        "pl": "Restauracja sushi w Toruniu",
        "uk": "Ресторан суші в Торуні",
        "en": "Sushi restaurant in Toruń"
      }
    },
    {
      "key": "addressLabel",
      "group": "contact",
      "label": "Address label",
      "type": "text",
      "sort_order": 20,
      "value": {
        "pl": "Adres:",
        "uk": "Адреса:",
        "en": "Address:"
      }
    },
    {
      "key": "phoneLabel",
      "group": "contact",
      "label": "Phone label",
      "type": "text",
      "sort_order": 21,
      "value": {
        "pl": "Telefon:",
        "uk": "Телефон:",
        "en": "Phone:"
      }
    },
    {
      "key": "hoursLabel",
      "group": "contact",
      "label": "Hours label",
      "type": "text",
      "sort_order": 22,
      "value": {
        "pl": "Godziny:",
        "uk": "Години:",
        "en": "Hours:"
      }
    },
    {
      "key": "hoursValue",
      "group": "contact",
      "label": "Hours value",
      "type": "text",
      "sort_order": 23,
      "value": {
        "pl": "Pon-Nd 12:00-21:00",
        "uk": "Пн-Нд 12:00-21:00",
        "en": "Mon-Sun 12:00-21:00"
      }
    },
    {
      "key": "socialTitle",
      "group": "contact",
      "label": "Social title",
      "type": "text",
      "sort_order": 24,
      "value": {
        "pl": "Social media",
        "uk": "Соцмережі",
        "en": "Social media"
      }
    },
    {
      "key": "socialText",
      "group": "contact",
      "label": "Social text",
      "type": "textarea",
      "sort_order": 25,
      "value": {
        "pl": "Obserwuj nas, jeśli chcesz zobaczyć nowe rolki, sezonowe zestawy sushi, ramen dnia i atmosferę z naszej sali w Toruniu.",
        "uk": "Стежте за нами, щоб бачити нові роли, сезонні суші-сети, рамен дня та атмосферу нашого ресторану в Торуні.",
        "en": "Follow us for new rolls, seasonal sushi sets, ramen specials and a glimpse of our restaurant atmosphere in Toruń."
      }
    },
    {
      "key": "takeawayTitle",
      "group": "contact",
      "label": "Takeaway title",
      "type": "text",
      "sort_order": 26,
      "value": {
        "pl": "Sushi na wynos",
        "uk": "Суші з собою",
        "en": "Sushi takeaway"
      }
    },
    {
      "key": "takeawayText",
      "group": "contact",
      "label": "Takeaway text",
      "type": "textarea",
      "sort_order": 27,
      "value": {
        "pl": "Zamów sushi online, wybierz ulubione rolki, ramen albo ciepłe danie i odbierz zamówienie wtedy, kiedy pasuje. Jesteśmy przy ul. Gen. Andersa 72 w Toruniu.",
        "uk": "Замовте суші онлайн, оберіть улюблені роли, рамен або гарячу страву й заберіть замовлення тоді, коли вам зручно. Ми на вул. Ген. Андерса 72 у Торуні.",
        "en": "Order sushi online, choose your favorite rolls, ramen or a warm dish, and pick it up when it suits you. Find us at Gen. Andersa 72 in Toruń."
      }
    },
    {
      "key": "takeawayButton",
      "group": "contact",
      "label": "Takeaway button",
      "type": "text",
      "sort_order": 28,
      "value": {
        "pl": "Zamów sushi online",
        "uk": "Замовити суші онлайн",
        "en": "Order sushi online"
      }
    },
    {
      "key": "detailsFallback",
      "group": "menu",
      "label": "Dish details fallback",
      "type": "text",
      "sort_order": 29,
      "value": {
        "pl": "Szczegóły pozycji w menu.",
        "uk": "Деталі позиції в меню.",
        "en": "Menu item details."
      }
    },
    {
      "key": "galleryItem",
      "group": "gallery",
      "label": "Gallery item label",
      "type": "text",
      "sort_order": 30,
      "value": {
        "pl": "Galeria",
        "uk": "Галерея",
        "en": "Gallery"
      }
    },
    {
      "key": "galleryDesc",
      "group": "gallery",
      "label": "Gallery modal description",
      "type": "textarea",
      "sort_order": 31,
      "value": {
        "pl": "Zdjęcie z galerii Umami Sushi & Food.",
        "uk": "Фото з галереї Umami Sushi & Food.",
        "en": "Photo from the Umami Sushi & Food gallery."
      }
    },
    {
      "key": "showPhoto",
      "group": "gallery",
      "label": "Show photo label",
      "type": "text",
      "sort_order": 32,
      "value": {
        "pl": "Pokaż zdjęcie",
        "uk": "Показати фото",
        "en": "Show photo"
      }
    },
    {
      "key": "prevPhoto",
      "group": "gallery",
      "label": "Previous photo label",
      "type": "text",
      "sort_order": 33,
      "value": {
        "pl": "Poprzednie zdjęcie",
        "uk": "Попереднє фото",
        "en": "Previous photo"
      }
    },
    {
      "key": "nextPhoto",
      "group": "gallery",
      "label": "Next photo label",
      "type": "text",
      "sort_order": 34,
      "value": {
        "pl": "Następne zdjęcie",
        "uk": "Наступне фото",
        "en": "Next photo"
      }
    },
    {
      "key": "photoChoice",
      "group": "gallery",
      "label": "Photo choice label",
      "type": "text",
      "sort_order": 35,
      "value": {
        "pl": "Wybór zdjęcia",
        "uk": "Вибір фото",
        "en": "Photo selection"
      }
    },
    {
      "key": "close",
      "group": "general",
      "label": "Close label",
      "type": "text",
      "sort_order": 36,
      "value": {
        "pl": "Zamknij",
        "uk": "Закрити",
        "en": "Close"
      }
    }
  ],
  "settings": [
    {
      "group": "branding",
      "key": "site_url",
      "label": "Site URL",
      "value": "https://umamisushifood.pl",
      "type": "url",
      "sort_order": 1
    },
    {
      "group": "branding",
      "key": "logo_image",
      "label": "Logo image",
      "value": "umami/logo.jpg",
      "type": "image",
      "sort_order": 2
    },
    {
      "group": "branding",
      "key": "background_desktop",
      "label": "Desktop background",
      "value": "umami/tlo3.png",
      "type": "image",
      "sort_order": 3
    },
    {
      "group": "branding",
      "key": "background_mobile",
      "label": "Mobile background",
      "value": "umami/tlo4.png",
      "type": "image",
      "sort_order": 4
    },
    {
      "group": "hero",
      "key": "hero_video",
      "label": "Hero video",
      "value": "umami/UMAMI.MP4",
      "type": "video",
      "sort_order": 5
    },
    {
      "group": "hero",
      "key": "hero_poster",
      "label": "Hero poster",
      "value": "umami/res1.png",
      "type": "image",
      "sort_order": 6
    },
    {
      "group": "contact",
      "key": "phone",
      "label": "Phone",
      "value": "+48 513 233 722",
      "type": "text",
      "sort_order": 7
    },
    {
      "group": "contact",
      "key": "phone_href",
      "label": "Phone href",
      "value": "tel:+48513233722",
      "type": "text",
      "sort_order": 8
    },
    {
      "group": "contact",
      "key": "address",
      "label": "Address",
      "value": "ul. Gen. Andersa 72, 87-100 Toruń",
      "type": "text",
      "sort_order": 9
    },
    {
      "group": "contact",
      "key": "order_url",
      "label": "Order URL",
      "value": "http://umamisushifood.goorder.pl/",
      "type": "url",
      "sort_order": 10
    },
    {
      "group": "contact",
      "key": "map_embed_url",
      "label": "Google Map embed URL",
      "value": "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2393.013512019404!2d18.6111919!3d52.9905789!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x470335d32c9f46d3%3A0x64fd6bff6758d0c6!2sUmami%20Sushi%20%26%20Food%20Toru%C5%84!5e0!3m2!1spl!2spl!4v1733770000000",
      "type": "url",
      "sort_order": 11
    },
    {
      "group": "seo",
      "key": "google_analytics_id",
      "label": "Google Analytics Measurement ID",
      "value": "",
      "type": "text",
      "sort_order": 12
    }
  ],
  "categories": [
    {
      "slug": "przystawki-zimne",
      "sort_order": 1,
      "name": {
        "pl": "Przystawki zimne",
        "uk": "Холодні закуски",
        "en": "Cold starters"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Edamame",
            "uk": "Едамаме",
            "en": "Edamame"
          },
          "description": {
            "pl": "Gotowane strączki soi z solą morską",
            "uk": "Відварені стручки сої з морською сіллю.",
            "en": "Boiled soybean pods with sea salt."
          },
          "price": "23 zł",
          "image": "umami/goorder/45-edamame.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/f8d87429-5149-4f86-b78a-bc648932df22",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Goma wakame",
            "uk": "Гома вакаме",
            "en": "Goma wakame"
          },
          "description": null,
          "price": "23 zł",
          "image": "umami/goorder/46-goma-wakame.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/234e19ce-3b0e-4a86-9c85-006208c241e3",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Kimchi",
            "uk": "Кімчі",
            "en": "Kimchi"
          },
          "description": {
            "pl": "Marynowane warzywa w stylu koreańskim",
            "uk": "Мариновані овочі в корейському стилі.",
            "en": "Korean-style pickled vegetables."
          },
          "price": "23 zł",
          "image": "umami/goorder/47-kimchi.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/f9c165ef-1239-409a-aeff-fab1ff43593b",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Tatar z łososia",
            "uk": "Тартар із лосося",
            "en": "Salmon tartare"
          },
          "description": null,
          "price": "54 zł",
          "image": "umami/goorder/51-tatar-z-lososia.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/9dc1238b-6a41-4d2e-85cd-2c9d59e564ae",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Tatar z tuńczyka",
            "uk": "Тартар із тунця",
            "en": "Tuna tartare"
          },
          "description": null,
          "price": "61 zł",
          "image": "umami/goorder/80-tatar-z-tunczyka.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/6843708c-d7db-48dc-a07f-063a68b584c9",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "przystawki-ciep-e",
      "sort_order": 2,
      "name": {
        "pl": "Przystawki ciepłe",
        "uk": "Гарячі закуски",
        "en": "Warm starters"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Ebi tempura",
            "uk": "Ебі темпура",
            "en": "Ebi tempura"
          },
          "description": {
            "pl": "Krewetki tygrysie w chrupiącym cieście podawane z majonezem truflowym",
            "uk": "Тигрові креветки в хрусткому тісті з трюфельним майонезом.",
            "en": "Tiger prawns in crispy batter, served with truffle mayo."
          },
          "price": "48 zł",
          "image": "umami/goorder/43-ebi-tempura.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/580bf130-eb8d-4a26-ada0-1ede14583edb",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Gyoza vege",
            "uk": "Овочева гьодза",
            "en": "Vegetable gyoza"
          },
          "description": {
            "pl": "z warzywami",
            "uk": "З овочами.",
            "en": "With vegetables."
          },
          "price": "29 zł",
          "image": "umami/goorder/73-gyoza-vege.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/ce664489-775e-4a87-85fb-e2bfc4da376b",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Gyoza wieprzowa",
            "uk": "Гьодза зі свининою",
            "en": "Pork gyoza"
          },
          "description": null,
          "price": "33 zł",
          "image": "umami/goorder/49-gyoza-wieprzowa.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/a8a3288a-b21e-4a3d-918f-fdae5435d518",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Harumaki ebi",
            "uk": "Харумакі з креветками",
            "en": "Prawn harumaki"
          },
          "description": {
            "pl": "sajgonki smażone na głębokim tłuszczu z krewetkami",
            "uk": "Хрусткі спрінг-роли з креветками.",
            "en": "Crispy spring rolls with prawns."
          },
          "price": "36 zł",
          "image": "umami/goorder/41-harumaki-ebi.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/85719d6a-0677-4e1c-b370-b5a542400d25",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Harumaki vege",
            "uk": "Овочеві харумакі",
            "en": "Vegetable harumaki"
          },
          "description": {
            "pl": "sajgonki smażone na głębokim tłuszczu z warzywami",
            "uk": "Хрусткі спрінг-роли з овочами.",
            "en": "Crispy spring rolls with vegetables."
          },
          "price": "30 zł",
          "image": "umami/goorder/40-harumaki-vege.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/6ed657c1-c025-4042-a4c9-9f334add4e00",
          "is_bestseller": false
        },
        {
          "sort_order": 6,
          "name": {
            "pl": "Kalmary w panko",
            "uk": "Кальмари в панко",
            "en": "Panko calamari"
          },
          "description": {
            "pl": "Krążki kalmarów w panierce panko z sosem spicy mayo",
            "uk": "Кільця кальмара в паніровці панко з гострим майонезним соусом.",
            "en": "Panko calamari rings with spicy mayo."
          },
          "price": "44 zł",
          "image": "umami/goorder/84-kalmary-w-panko.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/7287c5d2-6c0a-4832-8bf0-b1f53ab4e50f",
          "is_bestseller": false
        },
        {
          "sort_order": 7,
          "name": {
            "pl": "Tataki łosoś",
            "uk": "Татакі з лосося",
            "en": "Salmon tataki"
          },
          "description": null,
          "price": "56 zł",
          "image": "umami/goorder/48-tataki-losos.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/82892aad-813a-4c30-b429-379da85bcd57",
          "is_bestseller": false
        },
        {
          "sort_order": 8,
          "name": {
            "pl": "Tataki tuńczyk",
            "uk": "Татакі з тунця",
            "en": "Tuna tataki"
          },
          "description": null,
          "price": "58 zł",
          "image": "umami/goorder/81-tataki-tunczyk.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cec7c0ae-db33-4162-9eaa-4a9f4317d109",
          "is_bestseller": false
        },
        {
          "sort_order": 9,
          "name": {
            "pl": "Tempura mix",
            "uk": "Мікс темпура",
            "en": "Tempura mix"
          },
          "description": {
            "pl": "Łosoś, krewetki, kalmar oraz warzywa w chrupiącym cieście",
            "uk": "Лосось, креветки, кальмар і овочі в хрусткому тісті.",
            "en": "Salmon, prawns, squid and vegetables in crispy batter."
          },
          "price": "48 zł",
          "image": "umami/goorder/42-tempura-mix.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/55243f6f-57fa-460d-92e9-6b45668226c5",
          "is_bestseller": false
        },
        {
          "sort_order": 10,
          "name": {
            "pl": "Vege tempura",
            "uk": "Овочева темпура",
            "en": "Vegetable tempura"
          },
          "description": {
            "pl": "marchewka, cebula czerwona, papryka, por, pak choi",
            "uk": "Морква, червона цибуля, перець, порей, пак-чой.",
            "en": "Carrot, red onion, pepper, leek and pak choi."
          },
          "price": "38 zł",
          "image": "umami/goorder/44-vege-tempura.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/26546c26-e83b-4841-bf88-d8056c3b13ba",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "zupy",
      "sort_order": 3,
      "name": {
        "pl": "Zupy",
        "uk": "Супи",
        "en": "Soups"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Misoshiru",
            "uk": "Місошіру",
            "en": "Misoshiru"
          },
          "description": {
            "pl": "tradycyjna, japońska zupa na bazie bulionu warzywnego oraz pasty miso; może zawierać wodorosty wakame, grzyby i cebulkę",
            "uk": "Традиційний японський суп на овочевому бульйоні та пасті місо; може містити водорості вакаме, гриби й зелену цибулю.",
            "en": "A traditional Japanese soup based on vegetable broth and miso paste; may include wakame seaweed, mushrooms and spring onion."
          },
          "price": "22 zł",
          "image": "umami/goorder/10-misoshiru.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/7321b862-d3c6-4a4c-b48c-016af86b0238",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Tom Yum",
            "uk": "Том ям",
            "en": "Tom yum"
          },
          "description": {
            "pl": "Tajska zupa na bazie wywaru z trawy cytrynowej z dodatkiem mleka kokosowego, łososia, owoców morza, limonki oraz świeżej kolendry",
            "uk": "Тайський суп на бульйоні з лемонграсу з кокосовим молоком, лососем, морепродуктами, лаймом і свіжою кінзою.",
            "en": "Thai lemongrass broth with coconut milk, salmon, seafood, lime and fresh coriander."
          },
          "price": "38 zł",
          "image": "umami/goorder/11-tom-yum.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/2df24403-0891-4d7a-b7a8-9b5259631071",
          "is_bestseller": true
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Tom Yum Tori",
            "uk": "Том ям торі",
            "en": "Tom yum tori"
          },
          "description": {
            "pl": "Tajska zupa na bazie wywaru z trawy cytrynowej z dodatkiem mleka kokosowego, kurczaka, limonki oraz świeżej kolendry",
            "uk": "Тайський суп на бульйоні з лемонграсу з кокосовим молоком, куркою, лаймом і свіжою кінзою.",
            "en": "Thai lemongrass broth with coconut milk, chicken, lime and fresh coriander."
          },
          "price": "32 zł",
          "image": "umami/goorder/82-tom-yum-tori.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/dfed82f6-4aae-4c11-bb0f-3fe22b1c123d",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Wonton",
            "uk": "Вонтон",
            "en": "Wonton"
          },
          "description": {
            "pl": "Aromatyczny bulion z pierożkami wieprzowymi oraz warzywami julienne",
            "uk": "Ароматний бульйон зі свинячими пельменями та овочами жульєн.",
            "en": "Aromatic broth with pork dumplings and julienne vegetables."
          },
          "price": "32 zł",
          "image": "umami/goorder/12-wonton.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/e3aff9ac-633e-488d-8e82-2d48138e68aa",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "rameny",
      "sort_order": 4,
      "name": {
        "pl": "Rameny",
        "uk": "Рамен",
        "en": "Ramen"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Bifu Ramen",
            "uk": "Біфу рамен",
            "en": "Bifu ramen"
          },
          "description": {
            "pl": "Kremowy bulion z sezamowym tahini, smażoną wołowiną, marynowaną cebulką, kukurydzą oraz marynowanym jajkiem",
            "uk": "Кремовий бульйон із кунжутним тахіні, смаженою яловичиною, маринованою цибулею, кукурудзою та маринованим яйцем.",
            "en": "Creamy broth with sesame tahini, fried beef, pickled onion, corn and marinated egg."
          },
          "price": "58 zł",
          "image": "umami/goorder/6-bifu-ramen.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/6dfd6e9a-f805-4281-9fdc-10d1fde84445",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Chashu Ramen",
            "uk": "Чашу рамен",
            "en": "Chashu ramen"
          },
          "description": {
            "pl": "Aromatyczny bulion z soczystą karkówką, kawałkami wędzonego tofu, pędami bambusa oraz marynowanym jajkiem",
            "uk": "Ароматний бульйон із соковитою свинячою шийкою, шматочками копченого тофу, пагонами бамбука та маринованим яйцем.",
            "en": "Aromatic broth with juicy pork neck, smoked tofu, bamboo shoots and marinated egg."
          },
          "price": "54 zł",
          "image": "umami/goorder/1-chashu-ramen.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/226447df-119e-479b-b314-5782dc103ea2",
          "is_bestseller": true
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Shoyu Ramen",
            "uk": "Сьою рамен",
            "en": "Shoyu ramen"
          },
          "description": {
            "pl": "Grzybowy bulion z zapiekaną karkówką, grzybami shiitake, chrustem z marchewki oraz marynowanym jajkiem.",
            "uk": "Грибний бульйон із запеченою свинячою шийкою, грибами шиїтаке, морквяним чипсом і маринованим яйцем.",
            "en": "Mushroom broth with roasted pork neck, shiitake mushrooms, crispy carrot and marinated egg."
          },
          "price": "54 zł",
          "image": "umami/goorder/3-shoyu-ramen.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/4e1ff3f2-8afd-432f-8fcd-4dab594bcc3b",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Tori Ramen",
            "uk": "Торі рамен",
            "en": "Tori ramen"
          },
          "description": {
            "pl": "Aromatyczny bulion krewetkowy z grillowanym kurczakiem, porem, cebulo - cytryną oraz marynowanym jajkiem",
            "uk": "Ароматний креветковий бульйон із грильованою куркою, пореєм, цибулею-лимоном і маринованим яйцем.",
            "en": "Aromatic prawn broth with grilled chicken, leek, onion-lemon and marinated egg."
          },
          "price": "52 zł",
          "image": "umami/goorder/2-tori-ramen.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/c97a0305-fec8-4d9c-b042-ea34f1c0fae1",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Yasai Ramen",
            "uk": "Ясай рамен",
            "en": "Yasai ramen"
          },
          "description": {
            "pl": "Warzywny wywar z spicy miso z dodatkiem mleka kokosowego, kawałkami tofu inari, edamame, mennmą oraz marynowanym jajkiem",
            "uk": "Овочевий бульйон зі spicy miso, кокосовим молоком, тофу інарі, едамаме, менмою та маринованим яйцем.",
            "en": "Vegetable spicy miso broth with coconut milk, inari tofu, edamame, menma and marinated egg."
          },
          "price": "49 zł",
          "image": "umami/goorder/5-yasai-ramen.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/65986aad-4c84-437d-8365-5638a19dab29",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "dania-g-owne",
      "sort_order": 5,
      "name": {
        "pl": "Dania główne",
        "uk": "Основні страви",
        "en": "Main dishes"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Seafood Udon",
            "uk": "Удон з морепродуктами",
            "en": "Seafood udon"
          },
          "description": {
            "pl": "Pszenny makaron udon z owocami morza, warzywami i sosem ostrygowym",
            "uk": "Пшенична локшина удон із морепродуктами, овочами та устричним соусом.",
            "en": "Wheat udon noodles with seafood, vegetables and oyster sauce."
          },
          "price": "71 zł",
          "image": "umami/goorder/9-seafood-udon.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/fed27c39-6a9b-4029-889b-32d4f1b76932",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Soba z kurczakiem",
            "uk": "Соба з куркою",
            "en": "Soba with chicken"
          },
          "description": {
            "pl": "Gryczany makaron soba z kurczakiem, warzywami i sosem ostrygowym",
            "uk": "Гречана локшина соба з куркою, овочами та устричним соусом.",
            "en": "Buckwheat soba noodles with chicken, vegetables and oyster sauce."
          },
          "price": "59 zł",
          "image": "umami/goorder/4-soba-z-kurczakiem.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/5c6187d9-1025-4746-970e-c106bed86422",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Udon vege",
            "uk": "Овочевий удон",
            "en": "Vegetable udon"
          },
          "description": {
            "pl": "Pszenny makaron udon z warzywami i sosem sezamowym",
            "uk": "Пшенична локшина удон з овочами та кунжутним соусом.",
            "en": "Wheat udon noodles with vegetables and sesame sauce."
          },
          "price": "52 zł",
          "image": "umami/goorder/8-udon-vege.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/b1bb31e0-dacb-4349-87a6-5ed9573fe656",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Udon z wolowina",
            "uk": "Удон з яловичиною",
            "en": "Udon with beef"
          },
          "description": {
            "pl": "Pszenny makaron udon z wołowiną, fasolką szparagową, warzywami i sosem z fermentowanej soi",
            "uk": "Пшенична локшина удон із яловичиною, стручковою квасолею, овочами та соусом із ферментованої сої.",
            "en": "Wheat udon noodles with beef, green beans, vegetables and fermented soy sauce."
          },
          "price": "64 zł",
          "image": "umami/goorder/7-udon-z-wolowina.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/3e95e8b5-f255-444b-996b-2cd232525253",
          "is_bestseller": true
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Kurczak po koreańsku",
            "uk": "Курка по-корейськи",
            "en": "Korean-style chicken"
          },
          "description": {
            "pl": "Smażone kawałki kurczaka w słodko-pikantnym sosie podane z ryżem i sałatką wakame",
            "uk": "Смажені шматочки курки в солодко-гострому соусі, подаються з рисом і салатом вакаме.",
            "en": "Fried chicken pieces in a sweet and spicy sauce, served with rice and wakame salad."
          },
          "price": "49 zł",
          "image": "umami/goorder/50-kurczak-po-koreansku.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/8afa477d-0406-49e5-802a-cb230bd5ce49",
          "is_bestseller": true
        }
      ]
    },
    {
      "slug": "sashimi",
      "sort_order": 6,
      "name": {
        "pl": "Sashimi",
        "uk": "Сашимі",
        "en": "Sashimi"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Sashimi Duze",
            "uk": "Сашимі велике",
            "en": "Large sashimi"
          },
          "description": {
            "pl": "łosoś, tuńczyk, seriola, maślana (do wyboru)",
            "uk": "Лосось, тунець, серіола або масляна риба на вибір.",
            "en": "Salmon, tuna, yellowtail or butterfish to choose from."
          },
          "price": "117 zł",
          "image": "umami/goorder/79-sashimi-duze.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/1e5a140e-8074-4fc8-89cb-30112d9fa7e7",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Sashimi Małe",
            "uk": "Сашимі мале",
            "en": "Small sashimi"
          },
          "description": {
            "pl": "łosoś, tuńczyk, seriola, maślana (do wyboru)",
            "uk": "Лосось, тунець, серіола або масляна риба на вибір.",
            "en": "Salmon, tuna, yellowtail or butterfish to choose from."
          },
          "price": "43 zł",
          "image": "umami/goorder/59-sashimi-male.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/6f6a94bb-6658-4bd2-b864-722c2c5370fa",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Sashimi Średnie",
            "uk": "Сашимі середнє",
            "en": "Medium sashimi"
          },
          "description": {
            "pl": "łosoś, tuńczyk, seriola, maślana (do wyboru)",
            "uk": "Лосось, тунець, серіола або масляна риба на вибір.",
            "en": "Salmon, tuna, yellowtail or butterfish to choose from."
          },
          "price": "78 zł",
          "image": "umami/goorder/78-sashimi-srednie.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/78d4e2e1-e76f-43e1-be05-f5652d890307",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "nigiri",
      "sort_order": 7,
      "name": {
        "pl": "Nigiri",
        "uk": "Нігірі",
        "en": "Nigiri"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Nigiri łosoś",
            "uk": "Нігірі з лососем",
            "en": "Salmon nigiri"
          },
          "description": null,
          "price": "25 zł",
          "image": "umami/goorder/57-nigiri-losos.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/ee3353c6-757c-4d63-8433-57a4f39acba5",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Nigiri maślana",
            "uk": "Нігірі з масляною рибою",
            "en": "Butterfish nigiri"
          },
          "description": null,
          "price": "26 zł",
          "image": "umami/goorder/76-nigiri-maslana.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/8af56484-9aae-42ae-96ae-a4e25c4c745d",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Nigiri seriola",
            "uk": "Нігірі з серіолою",
            "en": "Yellowtail nigiri"
          },
          "description": null,
          "price": "31 zł",
          "image": "umami/goorder/75-nigiri-seriola.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/06d967d8-59d1-4ed0-930f-29c6479f3ad5",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Nigiri tuńczyk",
            "uk": "Нігірі з тунцем",
            "en": "Tuna nigiri"
          },
          "description": null,
          "price": "31 zł",
          "image": "umami/goorder/74-nigiri-tunczyk.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/449c61bd-58b0-4b02-97bd-f9d817d9c1dc",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Nigiri węgorz",
            "uk": "Нігірі з вугром",
            "en": "Eel nigiri"
          },
          "description": null,
          "price": "31 zł",
          "image": "umami/goorder/77-nigiri-wegorz.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/204ba484-386e-463d-9a88-93cf8a12f984",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "hosomaki",
      "sort_order": 8,
      "name": {
        "pl": "Hosomaki",
        "uk": "Хосомакі",
        "en": "Hosomaki"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Hosomak EBI TEN",
            "uk": "Хосомакі з креветкою в темпурі",
            "en": "Hosomaki with tempura prawn"
          },
          "description": {
            "pl": "krewetki w tempurze",
            "uk": "Креветки в темпурі.",
            "en": "Tempura prawns."
          },
          "price": "30 zł",
          "image": "umami/goorder/24-hosomak-ebi-ten.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/66aca742-91ba-49bd-99a2-d4ac99f245f7",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Hosomak SAKE",
            "uk": "Хосомакі з лососем",
            "en": "Salmon hosomaki"
          },
          "description": {
            "pl": "łosoś",
            "uk": "Лосось.",
            "en": "Salmon."
          },
          "price": "26 zł",
          "image": "umami/goorder/21-hosomak-sake.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/634cfa07-fcae-44d9-97c3-94aec009860a",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Hosomak SAKE YAKI",
            "uk": "Хосомакі з грильованим лососем",
            "en": "Grilled salmon hosomaki"
          },
          "description": {
            "pl": "grillowany łosoś",
            "uk": "Грильований лосось.",
            "en": "Grilled salmon."
          },
          "price": "26 zł",
          "image": "umami/goorder/23-hosomak-sake-yaki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/6346c15b-3043-4f99-b1e6-4c4369e35100",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Hosomak TEKKA",
            "uk": "Хосомакі з тунцем",
            "en": "Tuna hosomaki"
          },
          "description": {
            "pl": "tuńczyk",
            "uk": "Тунець.",
            "en": "Tuna."
          },
          "price": "32 zł",
          "image": "umami/goorder/22-hosomak-tekka.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/e8ded90c-6e47-454b-9090-bf7d3f9b2f99",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Hosomak UNAGI",
            "uk": "Хосомакі з грильованим вугром",
            "en": "Grilled eel hosomaki"
          },
          "description": {
            "pl": "grillowany węgorz",
            "uk": "Грильований вугор.",
            "en": "Grilled eel."
          },
          "price": "33 zł",
          "image": "umami/goorder/25-hosomak-unagi.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/f48f393a-fab8-4db7-a4cc-bcb8434b7158",
          "is_bestseller": false
        },
        {
          "sort_order": 6,
          "name": {
            "pl": "Hosomak vege",
            "uk": "Овочеві хосомакі",
            "en": "Vegetable hosomaki"
          },
          "description": null,
          "price": "22 zł",
          "image": "umami/goorder/26-hosomak-vege.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cad3f237-0369-444f-ba84-f52081ee72c3",
          "is_bestseller": false
        },
        {
          "sort_order": 7,
          "name": {
            "pl": "Hosomak vege Hosomak KAPPA",
            "uk": "Хосомакі каппа з огірком",
            "en": "Kappa cucumber hosomaki"
          },
          "description": null,
          "price": "22 zł",
          "image": "umami/goorder/27-hosomak-vege-hosomak-kappa.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cad3f237-0369-444f-ba84-f52081ee72c3",
          "is_bestseller": false
        },
        {
          "sort_order": 8,
          "name": {
            "pl": "Hosomak vege Hosomak Awokado",
            "uk": "Хосомакі з авокадо",
            "en": "Avocado hosomaki"
          },
          "description": null,
          "price": "22 zł",
          "image": "umami/goorder/28-hosomak-vege-hosomak-awokado.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cad3f237-0369-444f-ba84-f52081ee72c3",
          "is_bestseller": false
        },
        {
          "sort_order": 9,
          "name": {
            "pl": "Hosomak vege Hosomak Kampyo",
            "uk": "Хосомакі з кампьо",
            "en": "Kampyo hosomaki"
          },
          "description": null,
          "price": "22 zł",
          "image": "umami/goorder/29-hosomak-vege-hosomak-kampyo.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cad3f237-0369-444f-ba84-f52081ee72c3",
          "is_bestseller": false
        },
        {
          "sort_order": 10,
          "name": {
            "pl": "Hosomak vege Hosomak Cukinia w panko",
            "uk": "Хосомакі з цукіні в панко",
            "en": "Panko zucchini hosomaki"
          },
          "description": null,
          "price": "22 zł",
          "image": "umami/goorder/30-hosomak-vege-hosomak-cukinia-w-panko.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cad3f237-0369-444f-ba84-f52081ee72c3",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "gunkanmaki",
      "sort_order": 9,
      "name": {
        "pl": "Gunkanmaki",
        "uk": "Гунканмакі",
        "en": "Gunkanmaki"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Gunkanmaki salatka z krewetek w ogorku",
            "uk": "Гунканмакі з креветковим салатом в огірку",
            "en": "Gunkanmaki with prawn salad in cucumber"
          },
          "description": {
            "pl": "w ogórku",
            "uk": "В огірку.",
            "en": "In cucumber."
          },
          "price": "39 zł",
          "image": "umami/goorder/61-gunkanmaki-salatka-z-krewetek-w-ogorku.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/9232f3a8-2f48-4b09-8081-094bebad46a8",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Gunkanmaki tatar z lososia",
            "uk": "Гунканмакі з тартаром із лосося",
            "en": "Gunkanmaki with salmon tartare"
          },
          "description": {
            "pl": "w nori",
            "uk": "У норі.",
            "en": "In nori."
          },
          "price": "32 zł",
          "image": "umami/goorder/60-gunkanmaki-tatar-z-lososia.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/18a4ff0a-15d7-4876-96f6-32ef4be3d1b3",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Gunkanmaki tatar z tunczyka",
            "uk": "Гунканмакі з тартаром із тунця",
            "en": "Gunkanmaki with tuna tartare"
          },
          "description": {
            "pl": "w nori",
            "uk": "У норі.",
            "en": "In nori."
          },
          "price": "39 zł",
          "image": "umami/goorder/71-gunkanmaki-tatar-z-tunczyka.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/109a3ee9-40bc-4ceb-9003-4d82e5d54695",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Gunkanmaki z wegorzem w omlecie",
            "uk": "Гунканмакі з вугром в омлеті",
            "en": "Gunkanmaki with eel in omelette"
          },
          "description": null,
          "price": "39 zł",
          "image": "umami/goorder/72-gunkanmaki-z-wegorzem-w-omlecie.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/315567fd-ebae-492e-b56c-12c13cfebf55",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "futomaki",
      "sort_order": 10,
      "name": {
        "pl": "Futomaki",
        "uk": "Футомакі",
        "en": "Futomaki"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Futomak Ibodai goma",
            "uk": "Футомакі ібодай гома",
            "en": "Ibodai goma futomaki"
          },
          "description": {
            "pl": "ryba maślana smażona w sezamie, ogórek, szczypiorek, awokado, oshinko, kampyo",
            "uk": "Масляна риба, смажена в кунжуті, огірок, зелена цибуля, авокадо, ошинко, кампьо.",
            "en": "Butterfish fried in sesame, cucumber, chives, avocado, oshinko and kampyo."
          },
          "price": "39 zł",
          "image": "umami/goorder/15-futomak-ibodai-goma.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/c3622b86-b5da-47db-84ef-a8dce21e40e9",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Futomak Ebi Amondo",
            "uk": "Футомакі ебі амондо",
            "en": "Ebi amondo futomaki"
          },
          "description": {
            "pl": "krewetki smażone w migdałach, owoc, sos mango",
            "uk": "Креветки, смажені в мигдалі, фрукт і манговий соус.",
            "en": "Prawns fried in almonds, fruit and mango sauce."
          },
          "price": "40 zł",
          "image": "umami/goorder/85-futomak-ebi-amondo.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/cad1b611-8601-4c35-8d8c-2cbd290535e6",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Futomak EBI ten",
            "uk": "Футомакі з креветкою в темпурі",
            "en": "Futomaki with tempura prawn"
          },
          "description": {
            "pl": "krewetki w tempurze, awokado",
            "uk": "Креветки в темпурі, авокадо.",
            "en": "Tempura prawns and avocado."
          },
          "price": "39 zł",
          "image": "umami/goorder/17-futomak-ebi-ten.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/1ba12ebc-6911-459b-87b6-ec15663345e3",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Futomak EBI YAKI",
            "uk": "Футомакі з смаженою креветкою",
            "en": "Futomaki with butter-fried prawns"
          },
          "description": {
            "pl": "Krewetki smażone na maśle i sambalu, szparag, ogórek, awokado",
            "uk": "Креветки, смажені на маслі та самбалі, спаржа, огірок, авокадо.",
            "en": "Prawns fried in butter and sambal, asparagus, cucumber and avocado."
          },
          "price": "42 zł",
          "image": "umami/goorder/86-futomak-ebi-yaki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/2795558c-456e-406b-8e83-cb95781eb83c",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Futomak IKA ten",
            "uk": "Футомакі з кальмаром у темпурі",
            "en": "Futomaki with tempura squid"
          },
          "description": {
            "pl": "kalmary w tempurze, oshinko, spicy mayo",
            "uk": "Кальмари в темпурі, ошинко, гострий майонезний соус.",
            "en": "Tempura squid, oshinko and spicy mayo."
          },
          "price": "38 zł",
          "image": "umami/goorder/16-futomak-ika-ten.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/1ee2a987-2d52-49dc-9543-8177716a52f1",
          "is_bestseller": false
        },
        {
          "sort_order": 6,
          "name": {
            "pl": "Futomak maguro",
            "uk": "Футомакі з тунцем",
            "en": "Tuna futomaki"
          },
          "description": {
            "pl": "Tuńczyk, ogórek, szczypiorek, awokado, oshinko, kampyo, sriracha",
            "uk": "Тунець, огірок, зелена цибуля, авокадо, ошинко, кампьо, шрірача.",
            "en": "Tuna, cucumber, chives, avocado, oshinko, kampyo and sriracha."
          },
          "price": "44 zł",
          "image": "umami/goorder/14-futomak-maguro.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/0e8d488c-c8db-4305-b80d-9a3d74973803",
          "is_bestseller": false
        },
        {
          "sort_order": 7,
          "name": {
            "pl": "Futomak sake",
            "uk": "Футомакі з лососем",
            "en": "Salmon futomaki"
          },
          "description": {
            "pl": "łosoś, serek, awokado, ogórek",
            "uk": "Лосось, крем-сир, авокадо, огірок.",
            "en": "Salmon, cream cheese, avocado and cucumber."
          },
          "price": "35 zł",
          "image": "umami/goorder/13-futomak-sake.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/de5aeb5e-66fa-47f9-b590-48cc35de707a",
          "is_bestseller": false
        },
        {
          "sort_order": 8,
          "name": {
            "pl": "Futomak sake yaki",
            "uk": "Футомакі з грильованим лососем",
            "en": "Grilled salmon futomaki"
          },
          "description": {
            "pl": "grillowany łosoś, serek, awokado, ogórek",
            "uk": "Грильований лосось, крем-сир, авокадо, огірок.",
            "en": "Grilled salmon, cream cheese, avocado and cucumber."
          },
          "price": "35 zł",
          "image": "umami/goorder/19-futomak-sake-yaki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/f2b2a3e5-81f5-4ae1-92b7-9297abd02ead",
          "is_bestseller": false
        },
        {
          "sort_order": 9,
          "name": {
            "pl": "Futomak tilapia panko",
            "uk": "Футомакі з тілапією у панко",
            "en": "Panko tilapia futomaki"
          },
          "description": {
            "pl": "tilapia, majonez truflowy, ogórek, szczypiorek, awokado, oshinko, kampyo",
            "uk": "Тілапія, трюфельний майонез, огірок, зелена цибуля, авокадо, ошинко, кампьо.",
            "en": "Tilapia, truffle mayo, cucumber, chives, avocado, oshinko and kampyo."
          },
          "price": "34 zł",
          "image": "umami/goorder/18-futomak-tilapia-panko.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/5fdbcef3-36f1-4044-9618-124f03a6616c",
          "is_bestseller": false
        },
        {
          "sort_order": 10,
          "name": {
            "pl": "Futomak TOFU TATAKI",
            "uk": "Футомакі з тофу татакі",
            "en": "Tofu tataki futomaki"
          },
          "description": {
            "pl": "opalone tofu w truflowym sosie, ogórek, szczypiorek, awokado, oshinko, kampyo",
            "uk": "Обпалене тофу в трюфельному соусі, огірок, зелена цибуля, авокадо, ошинко, кампьо.",
            "en": "Torched tofu in truffle sauce, cucumber, chives, avocado, oshinko and kampyo."
          },
          "price": "28 zł",
          "image": "umami/goorder/88-futomak-tofu-tataki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/4bcecc1f-2309-445f-8c29-fe253c7e2738",
          "is_bestseller": false
        },
        {
          "sort_order": 11,
          "name": {
            "pl": "Futomak UNAGI amondo",
            "uk": "Футомакі з вугром у мигдалі",
            "en": "Almond eel futomaki"
          },
          "description": {
            "pl": "węgorz smażony w migdałach, omlet, owoc, kabayaki",
            "uk": "Вугор, смажений у мигдалі, омлет, фрукт, кабаякі.",
            "en": "Eel fried in almonds, omelette, fruit and kabayaki."
          },
          "price": "43 zł",
          "image": "umami/goorder/20-futomak-unagi-amondo.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/1c49f98d-6ad1-4297-94d9-1c3c88988bb3",
          "is_bestseller": false
        },
        {
          "sort_order": 12,
          "name": {
            "pl": "Futomak VEGE&SZPARAG",
            "uk": "Футомакі з овочами та спаржею",
            "en": "Vegetable and asparagus futomaki"
          },
          "description": {
            "pl": "ogórek, szczypiorek, awokado, oshinko, kampyo, szparag smażony w tempurze",
            "uk": "Огірок, зелена цибуля, авокадо, ошинко, кампьо, спаржа в темпурі.",
            "en": "Cucumber, chives, avocado, oshinko, kampyo and tempura asparagus."
          },
          "price": "28 zł",
          "image": "umami/goorder/87-futomak-vege-szparag.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/364dcfb4-aa7c-45a4-9e2f-6f23eff827c2",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "rolki-smazone",
      "sort_order": 11,
      "name": {
        "pl": "Rolki smażone",
        "uk": "Смажені роли",
        "en": "Fried rolls"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Rolka smażona KREWETKI w mixie nasion",
            "uk": "Смажений рол з креветками в міксі насіння",
            "en": "Fried roll with prawns in a seed mix"
          },
          "description": null,
          "price": "41 zł",
          "image": "umami/goorder/37-rolka-smazona-krewetki-w-mixie-nasion.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/7d19a9f1-49d7-4ca1-8629-0d8f38fc23b8",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Rolka smażona łosoś w tempurze",
            "uk": "Смажений рол з лососем у темпурі",
            "en": "Fried roll with tempura salmon"
          },
          "description": null,
          "price": "39 zł",
          "image": "umami/goorder/36-rolka-smazona-losos-w-tempurze.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/4e4b08ed-4694-431c-bd6a-5f36eab8ba3d",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Rolka smażona ryba maślana w migdałach",
            "uk": "Смажений рол з масляною рибою в мигдалі",
            "en": "Fried roll with almond butterfish"
          },
          "description": null,
          "price": "39 zł",
          "image": "umami/goorder/68-rolka-smazona-ryba-maslana-w-migdalach.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/a743a43f-e726-4357-91a6-f897c2ffd180",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Rolka smażona świeże warzywa w tempurze",
            "uk": "Смажений рол зі свіжими овочами в темпурі",
            "en": "Fried roll with fresh tempura vegetables"
          },
          "description": {
            "pl": "ogórek, szczypiorek, awokado, oshinko, kampyo, w tempurze",
            "uk": "Огірок, зелена цибуля, авокадо, ошинко, кампьо, у темпурі.",
            "en": "Cucumber, chives, avocado, oshinko and kampyo in tempura."
          },
          "price": "33 zł",
          "image": "umami/goorder/69-rolka-smazona-swieze-warzywa-w-tempurze.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/7d656a6c-3272-4de2-8e38-c15cc4219a28",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Rolka smażona TATAR w panko",
            "uk": "Смажений рол з тартаром у панко",
            "en": "Fried roll with panko tartare"
          },
          "description": null,
          "price": "40 zł",
          "image": "umami/goorder/70-rolka-smazona-tatar-w-panko.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/a2861033-37df-4ee3-a963-26de5035edc6",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "freshmaki",
      "sort_order": 12,
      "name": {
        "pl": "Freshmaki",
        "uk": "Фрешмакі",
        "en": "Freshmaki"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Datemaki ŁOSOŚ",
            "uk": "Датемакі з лососем",
            "en": "Salmon datemaki"
          },
          "description": {
            "pl": "Łosoś, liczi, serek, awokado",
            "uk": "Лосось, лічі, крем-сир, авокадо.",
            "en": "Salmon, lychee, cream cheese and avocado."
          },
          "price": "39 zł",
          "image": "umami/goorder/52-datemaki-losos.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/db70a08b-a432-4a4c-8bd4-fadd3a9ef520",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Freshmaki Łosoś",
            "uk": "Фрешмакі з лососем",
            "en": "Salmon freshmaki"
          },
          "description": {
            "pl": "Szparag w tempurze, ogórek, szczypiorek, awokado, oshinko, kampyo",
            "uk": "Спаржа в темпурі, огірок, зелена цибуля, авокадо, ошинко, кампьо.",
            "en": "Tempura asparagus, cucumber, chives, avocado, oshinko and kampyo."
          },
          "price": "39 zł",
          "image": "umami/goorder/53-freshmaki-losos.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/12f9cd27-0e60-4453-affa-29fb6d3e14b0",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Freshmaki TATAR Z ŁOSOSIA",
            "uk": "Фрешмакі з тартаром із лосося",
            "en": "Freshmaki with salmon tartare"
          },
          "description": {
            "pl": "Tatar, por, cebula, marchewka w tempurze",
            "uk": "Тартар, порей, цибуля, морква в темпурі.",
            "en": "Tartare, leek, onion and tempura carrot."
          },
          "price": "39 zł",
          "image": "umami/goorder/56-freshmaki-tatar-z-lososia.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/dddd4874-f814-439f-9ea8-097493de0133",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Freshmaki Tuńczyk i Seriola",
            "uk": "Фрешмакі з тунцем і серіолою",
            "en": "Freshmaki with tuna and yellowtail"
          },
          "description": {
            "pl": "Krewetki i szparag smażone na maśle i sambalu",
            "uk": "Креветки та спаржа, смажені на маслі й самбалі.",
            "en": "Prawns and asparagus fried in butter and sambal."
          },
          "price": "48 zł",
          "image": "umami/goorder/54-freshmaki-tunczyk-i-seriola.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/03773126-4550-4f8e-ba1e-67e01c05add0",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Freshmaki WĘGORZ",
            "uk": "Фрешмакі з вугром",
            "en": "Eel freshmaki"
          },
          "description": {
            "pl": "Krewetka w panko, awokado",
            "uk": "Креветка в панко, авокадо.",
            "en": "Panko prawn and avocado."
          },
          "price": "48 zł",
          "image": "umami/goorder/55-freshmaki-wegorz.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/0c1a30ff-66d2-4cf4-895c-f161d2f76e74",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "uramaki",
      "sort_order": 13,
      "name": {
        "pl": "Uramaki",
        "uk": "Урамакі",
        "en": "Uramaki"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Uramaki Hoso ten&Spicy maguro",
            "uk": "Урамакі хосо тен і гострий магуро",
            "en": "Hoso ten and spicy maguro uramaki"
          },
          "description": {
            "pl": "hosomaki w tempurze, pikantny siekany tuńczyk",
            "uk": "Хосомакі в темпурі, гострий рублений тунець.",
            "en": "Tempura hosomaki with spicy chopped tuna."
          },
          "price": "55 zł",
          "image": "umami/goorder/96-uramaki-hoso-ten-spicy-maguro.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/d071b498-5d3c-4731-8ba4-30939eb60893",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Uramak Dragon",
            "uk": "Урамакі Дракон",
            "en": "Dragon uramaki"
          },
          "description": {
            "pl": "krewetki w panko, awokado, grillowany węgorz, ikra, sos kabayaki",
            "uk": "Креветки в панко, авокадо, грильований вугор, ікра, соус кабаякі.",
            "en": "Panko prawns, avocado, grilled eel, roe and kabayaki sauce."
          },
          "price": "58 zł",
          "image": "umami/goorder/31-uramak-dragon.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/2d5c0e06-1f9b-4f27-8cef-edb82a17e443",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Uramaki EBI PANKO&AWOKADO",
            "uk": "Урамакі з креветкою в панко та авокадо",
            "en": "Panko prawn and avocado uramaki"
          },
          "description": {
            "pl": "krewetki w panko, awokado, spicy mayo, kabayaki",
            "uk": "Креветки в панко, авокадо, гострий майонезний соус, кабаякі.",
            "en": "Panko prawns, avocado, spicy mayo and kabayaki."
          },
          "price": "55 zł",
          "image": "umami/goorder/92-uramaki-ebi-panko-awokado.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/fa7850c4-5482-4f0d-a2e5-38f0aeacf518",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Uramak MAGURO TATAKI",
            "uk": "Урамакі магуро татакі",
            "en": "Maguro tataki uramaki"
          },
          "description": {
            "pl": "małże w tempurze, awokado, opalany tuńczyk, majo trufla",
            "uk": "Мідії в темпурі, авокадо, обпалений тунець, трюфельний майонез.",
            "en": "Tempura mussels, avocado, torched tuna and truffle mayo."
          },
          "price": "57 zł",
          "image": "umami/goorder/95-uramak-maguro-tataki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/e363262c-c507-4298-82cc-b5415798b332",
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Uramak Philadelphia",
            "uk": "Урамакі Філадельфія",
            "en": "Philadelphia uramaki"
          },
          "description": {
            "pl": "łosoś, serek, awokado",
            "uk": "Лосось, крем-сир, авокадо.",
            "en": "Salmon, cream cheese and avocado."
          },
          "price": "54 zł",
          "image": "umami/goorder/33-uramak-philadelphia.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/f22ba4ec-7585-498e-930c-c59d58a49dfd",
          "is_bestseller": false
        },
        {
          "sort_order": 6,
          "name": {
            "pl": "Uramak Rainbow",
            "uk": "Урамакі Рейнбоу",
            "en": "Rainbow uramaki"
          },
          "description": {
            "pl": "krewetki w miksie nasion, awokado, łosoś, tuńczyk, seriola, maślana",
            "uk": "Креветки в міксі насіння, авокадо, лосось, тунець, серіола, масляна риба.",
            "en": "Prawns in a seed mix, avocado, salmon, tuna, yellowtail and butterfish."
          },
          "price": "56 zł",
          "image": "umami/goorder/32-uramak-rainbow.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/7fb3f54c-abd3-4936-870d-91d32da584ea",
          "is_bestseller": false
        },
        {
          "sort_order": 7,
          "name": {
            "pl": "Uramak SAKE ABURI",
            "uk": "Урамакі саке абурі",
            "en": "Sake aburi uramaki"
          },
          "description": {
            "pl": "por, cebula, marchewka w tempurze, opalany łosoś z kabayaki, sos spicy mayo, szczypiorek",
            "uk": "Порей, цибуля, морква в темпурі, обпалений лосось із кабаякі, гострий майонезний соус, зелена цибуля.",
            "en": "Leek, onion, tempura carrot, torched salmon with kabayaki, spicy mayo and chives."
          },
          "price": "54 zł",
          "image": "umami/goorder/94-uramak-sake-aburi.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/eca47f3f-0f7a-4059-83fe-23713a1d617e",
          "is_bestseller": false
        },
        {
          "sort_order": 8,
          "name": {
            "pl": "Uramak Sake Tataki&Truffle",
            "uk": "Урамакі саке татакі з трюфелем",
            "en": "Sake tataki and truffle uramaki"
          },
          "description": {
            "pl": "ogórek, szczypiorek, awokado, oshinko, kampyo, opalany łosoś, sos truflowy",
            "uk": "Огірок, зелена цибуля, авокадо, ошинко, кампьо, обпалений лосось, трюфельний соус.",
            "en": "Cucumber, chives, avocado, oshinko, kampyo, torched salmon and truffle sauce."
          },
          "price": "54 zł",
          "image": "umami/goorder/34-uramak-sake-tataki-truffle.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/356a28bc-e5d5-4dc8-a8f3-9c06577d44b6",
          "is_bestseller": false
        },
        {
          "sort_order": 9,
          "name": {
            "pl": "Uramak SAKE YAKI&AWOKADO",
            "uk": "Урамакі з грильованим лососем і авокадо",
            "en": "Grilled salmon and avocado uramaki"
          },
          "description": {
            "pl": "łosoś grillowany, serek, awokado",
            "uk": "Грильований лосось, крем-сир, авокадо.",
            "en": "Grilled salmon, cream cheese and avocado."
          },
          "price": "53 zł",
          "image": "umami/goorder/90-uramak-sake-yaki-awokado.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/916c2f21-97aa-4835-9848-c61939f24719",
          "is_bestseller": false
        },
        {
          "sort_order": 10,
          "name": {
            "pl": "Uramak SAKE&AWOKADO",
            "uk": "Урамакі з лососем і авокадо",
            "en": "Salmon and avocado uramaki"
          },
          "description": {
            "pl": "łosoś surowy, serek, awokado",
            "uk": "Сирий лосось, крем-сир, авокадо.",
            "en": "Raw salmon, cream cheese and avocado."
          },
          "price": "53 zł",
          "image": "umami/goorder/89-uramak-sake-awokado.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/c22b9ff4-deee-4744-a7dd-596dce82d065",
          "is_bestseller": false
        },
        {
          "sort_order": 11,
          "name": {
            "pl": "Uramak SAKE&EBI YAKI",
            "uk": "Урамакі з лососем і ебі які",
            "en": "Salmon and ebi yaki uramaki"
          },
          "description": {
            "pl": "szparag w tempurze, krewetki gotowane, łosoś, majonez truflowy",
            "uk": "Спаржа в темпурі, варені креветки, лосось, трюфельний майонез.",
            "en": "Tempura asparagus, boiled prawns, salmon and truffle mayo."
          },
          "price": "54 zł",
          "image": "umami/goorder/91-uramak-sake-ebi-yaki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/76fb6841-c78c-40cc-8dbe-b92a282a3fae",
          "is_bestseller": false
        },
        {
          "sort_order": 12,
          "name": {
            "pl": "Uramak Tatar z łososia",
            "uk": "Урамакі з тартаром із лосося",
            "en": "Uramaki with salmon tartare"
          },
          "description": {
            "pl": "por, cebula, marchewka w tempurze, tatar z łososia, ikra, sos spicy mayo",
            "uk": "Порей, цибуля, морква в темпурі, тартар із лосося, ікра, гострий майонезний соус.",
            "en": "Leek, onion, tempura carrot, salmon tartare, roe and spicy mayo."
          },
          "price": "54 zł",
          "image": "umami/goorder/93-uramak-tatar-z-lososia.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/9959e5d9-3b4f-4bce-a6d7-207a9ea504a7",
          "is_bestseller": true
        },
        {
          "sort_order": 13,
          "name": {
            "pl": "Uramak VEGE AWOKADO",
            "uk": "Овочеві урамакі з авокадо",
            "en": "Vegetable avocado uramaki"
          },
          "description": {
            "pl": "por, cebula, marchewka w tempurze, awokado",
            "uk": "Порей, цибуля, морква в темпурі, авокадо.",
            "en": "Leek, onion, tempura carrot and avocado."
          },
          "price": "44 zł",
          "image": "umami/goorder/97-uramak-vege-awokado.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/1b5293e6-9e2b-4844-ae43-15aa12797444",
          "is_bestseller": false
        },
        {
          "sort_order": 14,
          "name": {
            "pl": "Uramak Vege&Guacamole",
            "uk": "Овочеві урамакі з гуакамоле",
            "en": "Vegetable guacamole uramaki"
          },
          "description": {
            "pl": "ogórek, szczypiorek, awokado, oshinko, kampyo, guacamole",
            "uk": "Огірок, зелена цибуля, авокадо, ошинко, кампьо, гуакамоле.",
            "en": "Cucumber, chives, avocado, oshinko, kampyo and guacamole."
          },
          "price": "44 zł",
          "image": "umami/goorder/35-uramak-vege-guacamole.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/3a200a86-2bf5-4a8b-9ed7-28a134b88d62",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "zestawy-sushi",
      "sort_order": 14,
      "name": {
        "pl": "Zestawy sushi",
        "uk": "Суші-сети",
        "en": "Sushi sets"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "ARAKAWA",
            "uk": "Аракава",
            "en": "Arakawa"
          },
          "description": {
            "pl": "8x Uramaki łosoś & awokado\n6x Hosomaki seriola\n2x Nigiri łosoś\n6x Futomaki tuńczyk\n4x Datemaki łosoś & liczi",
            "uk": "8x урамакі з лососем і авокадо\n6x хосомакі з серіолою\n2x нігірі з лососем\n6x футомакі з тунцем\n4x датемакі з лососем і лічі",
            "en": "8x salmon and avocado uramaki\n6x yellowtail hosomaki\n2x salmon nigiri\n6x tuna futomaki\n4x salmon and lychee datemaki"
          },
          "price": "171 zł",
          "image": "umami/goorder/98-arakawa.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/e89d8e99-dc9d-4d47-bc54-16eb59ff782c",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "DELUXE.",
            "uk": "Делюкс",
            "en": "Deluxe"
          },
          "description": {
            "pl": "Futomaki Łosoś\n6 kawałków\nFutomaki z rybą maślaną w sezamie\n6 kawałków\nUramaki tilapia w panko & awokado\n8 kawałków\nUramaki warzywa w tempurze & tatar z łososia\n8 kawałków\nHosomaki w tempurze z łososiem\n8 kawałków",
            "uk": "Футомакі з лососем\n6 шматочків\nФутомакі з масляною рибою в кунжуті\n6 шматочків\nУрамакі з тілапією у панко та авокадо\n8 шматочків\nУрамакі з овочами в темпурі та тартаром із лосося\n8 шматочків\nХосомакі з лососем у темпурі\n8 шматочків",
            "en": "Salmon futomaki\n6 pieces\nSesame butterfish futomaki\n6 pieces\nPanko tilapia and avocado uramaki\n8 pieces\nTempura vegetable and salmon tartare uramaki\n8 pieces\nTempura salmon hosomaki\n8 pieces"
          },
          "price": "181 zł",
          "image": "umami/goorder/64-deluxe.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/625b4dba-ec8e-4887-aacc-b5089b67b4dc",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Fish Harmony",
            "uk": "Фіш Хармоні",
            "en": "Fish Harmony"
          },
          "description": {
            "pl": "Futomaki grillowany Łosoś\n6 kawałków\nFutomaki tilapia w panko\n6 kawałków\nUramaki warzywa w tempurze & opalany łosoś\n4 kawałki\nHosomaki ryba maślana smażone w migdałach\n8 kawałków",
            "uk": "Футомакі з грильованим лососем\n6 шматочків\nФутомакі з тілапією в панко\n6 шматочків\nУрамакі з овочами в темпурі та обпаленим лососем\n4 шматочки\nХосомакі з масляною рибою в мигдалі\n8 шматочків",
            "en": "Grilled salmon futomaki\n6 pieces\nPanko tilapia futomaki\n6 pieces\nTempura vegetable and torched salmon uramaki\n4 pieces\nAlmond-fried butterfish hosomaki\n8 pieces"
          },
          "price": "142 zł",
          "image": "umami/goorder/39-fish-harmony.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/ac4449e7-72e0-49bc-8674-e3686d492124",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Master Roll",
            "uk": "Майстер рол",
            "en": "Master Roll"
          },
          "description": {
            "pl": "Futomaki łosoś\n6 kawałków\nFutomaki tuńczyk\n6 kawałków\nFutomaki tilapia w panko\n6 kawałków\nFutomaki kalmar w tempurze\n6 kawałków\nUramaki krewetka w tempurze & tatar z łososia\n4 kawałki\nUramaki krewetka w panko & grillowany węgorz\n4 kawałki\nUramaki ze świeżymi składnikami",
            "uk": "Футомакі з лососем\n6 шматочків\nФутомакі з тунцем\n6 шматочків\nФутомакі з тілапією в панко\n6 шматочків\nФутомакі з кальмаром у темпурі\n6 шматочків\nУрамакі з креветкою в темпурі та тартаром із лосося\n4 шматочки\nУрамакі з креветкою в панко та грильованим вугром\n4 шматочки\nУрамакі зі свіжими інгредієнтами",
            "en": "Salmon futomaki\n6 pieces\nTuna futomaki\n6 pieces\nPanko tilapia futomaki\n6 pieces\nTempura squid futomaki\n6 pieces\nTempura prawn and salmon tartare uramaki\n4 pieces\nPanko prawn and grilled eel uramaki\n4 pieces\nUramaki with fresh ingredients"
          },
          "price": "241 zł",
          "image": "umami/goorder/62-master-roll.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/30caa361-2df4-459b-9fd1-16453f0c1361",
          "is_bestseller": true
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Sakura Mini.",
            "uk": "Сакура Міні",
            "en": "Sakura Mini"
          },
          "description": {
            "pl": "Futomaki łosoś\n6 kawałków\nFutomaki tilapia w tempurze\n6 kawałków\nUramaki pikantny siekany tuńczyk\n4 kawałki\nUramaki warzywa w tempurze & tatar z łososia\n4 kawałki",
            "uk": "Футомакі з лососем\n6 шматочків\nФутомакі з тілапією в темпурі\n6 шматочків\nУрамакі з гострим рубленим тунцем\n4 шматочки\nУрамакі з овочами в темпурі та тартаром із лосося\n4 шматочки",
            "en": "Salmon futomaki\n6 pieces\nTempura tilapia futomaki\n6 pieces\nSpicy chopped tuna uramaki\n4 pieces\nTempura vegetable and salmon tartare uramaki\n4 pieces"
          },
          "price": "142 zł",
          "image": "umami/goorder/38-sakura-mini.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/3128bfea-364b-44c2-bd36-9b133a347125",
          "is_bestseller": false
        },
        {
          "sort_order": 6,
          "name": {
            "pl": "TEMPURA SET",
            "uk": "Темпура сет",
            "en": "Tempura set"
          },
          "description": {
            "pl": "Futomaki w tempurze z łososiem\n6 kawałków\nFutomaki w tempurze z rybą maślaną\n6 kawałków\nFutomaki w panko z tatarem z łososia\n6 kawałków\nHosomaki w migdałach z łososiem\n8 kawałków",
            "uk": "Футомакі з лососем у темпурі\n6 шматочків\nФутомакі з масляною рибою в темпурі\n6 шматочків\nФутомакі в панко з тартаром із лосося\n6 шматочків\nХосомакі з лососем у мигдалі\n8 шматочків",
            "en": "Tempura salmon futomaki\n6 pieces\nTempura butterfish futomaki\n6 pieces\nPanko futomaki with salmon tartare\n6 pieces\nAlmond salmon hosomaki\n8 pieces"
          },
          "price": "181 zł",
          "image": "umami/goorder/99-tempura-set.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/a189888c-3b37-4f05-b37b-eef89bfb2b48",
          "is_bestseller": false
        },
        {
          "sort_order": 7,
          "name": {
            "pl": "UMAMI.",
            "uk": "Умамі",
            "en": "Umami"
          },
          "description": {
            "pl": "Futomaki łosoś grillowany\n6 kawałków\nFutomaki tuńczyk\n6 kawałków\nFutomaki łosoś\n6 kawałków\nFutomaki maślana w sezamie\n6 kawałków\nFutomaki węgorz w migdałach\n6 kawałków\nUramaki warzywa w tempurze & tatar z łososia\n8 kawałków\nUramaki tilapia w panko",
            "uk": "Футомакі з грильованим лососем\n6 шматочків\nФутомакі з тунцем\n6 шматочків\nФутомакі з лососем\n6 шматочків\nФутомакі з масляною рибою в кунжуті\n6 шматочків\nФутомакі з вугром у мигдалі\n6 шматочків\nУрамакі з овочами в темпурі та тартаром із лосося\n8 шматочків\nУрамакі з тілапією у панко",
            "en": "Grilled salmon futomaki\n6 pieces\nTuna futomaki\n6 pieces\nSalmon futomaki\n6 pieces\nSesame butterfish futomaki\n6 pieces\nAlmond eel futomaki\n6 pieces\nTempura vegetable and salmon tartare uramaki\n8 pieces\nPanko tilapia uramaki"
          },
          "price": "351 zł",
          "image": "umami/goorder/63-umami.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/4c3fd473-f0da-4615-98e8-ecd9e2c9d436",
          "is_bestseller": false
        },
        {
          "sort_order": 8,
          "name": {
            "pl": "Vege Mini",
            "uk": "Веге Міні",
            "en": "Vege Mini"
          },
          "description": {
            "pl": "Futomaki vege\n6 kawałków\nHosomaki w migdałach z kampyo\n8 kawałków\nUramaki vege & guacamole\n4 kawałki\nUramaki vege awokado\n4 kawałki",
            "uk": "Овочеві футомакі\n6 шматочків\nХосомакі з кампьо в мигдалі\n8 шматочків\nОвочеві урамакі з гуакамоле\n4 шматочки\nОвочеві урамакі з авокадо\n4 шматочки",
            "en": "Vegetable futomaki\n6 pieces\nAlmond kampyo hosomaki\n8 pieces\nVegetable guacamole uramaki\n4 pieces\nVegetable avocado uramaki\n4 pieces"
          },
          "price": "97 zł",
          "image": "umami/goorder/65-vege-mini.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/3b19c99e-6d99-4993-a785-d891a75aebb8",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "dodatki",
      "sort_order": 15,
      "name": {
        "pl": "Dodatki",
        "uk": "Додатки",
        "en": "Add-ons"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Dodatkowe wasabi",
            "uk": "Додаткове васабі",
            "en": "Extra wasabi"
          },
          "description": null,
          "price": "5 zł",
          "image": "umami/goorder/101-dodatkowe-wasabi.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/17a77218-18bd-4a4a-9bed-b1fec07b63a9",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Dodatkowy imbir",
            "uk": "Додатковий імбир",
            "en": "Extra ginger"
          },
          "description": null,
          "price": "5 zł",
          "image": "umami/goorder/100-dodatkowy-imbir.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/0032e9da-5766-477a-a5c2-0af503b0b391",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Sos kabajaki",
            "uk": "Соус кабаякі",
            "en": "Kabayaki sauce"
          },
          "description": null,
          "price": "5 zł",
          "image": "umami/goorder/67-sos-kabajaki.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/80022bd5-0dba-402d-908b-177110300418",
          "is_bestseller": false
        },
        {
          "sort_order": 4,
          "name": {
            "pl": "Sos sojowy",
            "uk": "Соєвий соус",
            "en": "Soy sauce"
          },
          "description": null,
          "price": "3 zł",
          "image": null,
          "source_image": null,
          "is_bestseller": false
        },
        {
          "sort_order": 5,
          "name": {
            "pl": "Sos spicy majo",
            "uk": "Гострий майонезний соус",
            "en": "Spicy mayo sauce"
          },
          "description": null,
          "price": "5 zł",
          "image": "umami/goorder/83-sos-spicy-majo.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/c39bee52-cacd-49d0-9ec3-4933561c5015",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "napoje",
      "sort_order": 16,
      "name": {
        "pl": "Napoje",
        "uk": "Напої",
        "en": "Drinks"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Pepsi 0,33",
            "uk": "Pepsi 0,33",
            "en": "Pepsi 0.33"
          },
          "description": {
            "pl": "W cenę jest wliczona kaucja 0,50 zł za puszkę",
            "uk": "У ціну включено заставу 0,50 PLN за банку.",
            "en": "The price includes a 0.50 PLN can deposit."
          },
          "price": "8,5 zł",
          "image": "umami/goorder/102-pepsi-0-33.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/dcb2973f-f3c9-4865-9a86-a724126bedbe",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Lemoniada 0,5",
            "uk": "Лимонад 0,5",
            "en": "Lemonade 0.5"
          },
          "description": null,
          "price": "16 zł",
          "image": "umami/goorder/103-lemoniada-0-5.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/8f8fa218-fa5c-4a7d-bc94-89628f04335a",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "menu-sezonowe",
      "sort_order": 17,
      "name": {
        "pl": "Menu sezonowe",
        "uk": "Сезонне меню",
        "en": "Seasonal menu"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "Szparagi w maślanej emulsji",
            "uk": "Спаржа в масляній емульсії",
            "en": "Asparagus in butter emulsion"
          },
          "description": {
            "pl": "miso holendez, łosoś glawrax, jajko w panko",
            "uk": "Місо-голандез, лосось gravlax, яйце в панко.",
            "en": "Miso hollandaise, gravlax salmon and panko egg."
          },
          "price": "37 zł",
          "image": "umami/goorder/105-szparagi-w-maslanej-emulsji.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/dd9e9d99-7673-45a0-97c7-c65eb6daea88",
          "is_bestseller": false
        },
        {
          "sort_order": 2,
          "name": {
            "pl": "Uramaki ze szparagami",
            "uk": "Урамакі зі спаржею",
            "en": "Uramaki with asparagus"
          },
          "description": null,
          "price": "47 zł",
          "image": "umami/goorder/104-uramaki-ze-szparagami.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/509000a7-13ad-4608-ae36-31ed9d197171",
          "is_bestseller": false
        },
        {
          "sort_order": 3,
          "name": {
            "pl": "Wolno gotowany boczek w sosie Hoisin",
            "uk": "Повільно томлений бекон у соусі хойсін",
            "en": "Slow-cooked pork belly in hoisin sauce"
          },
          "description": {
            "pl": "opiekane ziemniaczki, groszek cukrowy, mini kukurydza oraz surówka z młodej kapusty",
            "uk": "Запечена картопля, цукровий горошок, мінікукурудза та салат із молодої капусти.",
            "en": "Roasted potatoes, sugar snap peas, baby corn and young cabbage slaw."
          },
          "price": "47 zł",
          "image": "umami/goorder/106-wolno-gotowany-boczek-w-sosie-hoisin.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/e69f3c17-0ac4-468d-870a-c68feb6fb9df",
          "is_bestseller": false
        }
      ]
    },
    {
      "slug": "dania-dla-dzieci",
      "sort_order": 18,
      "name": {
        "pl": "Dania dla dzieci",
        "uk": "Страви для дітей",
        "en": "Kids menu"
      },
      "items": [
        {
          "sort_order": 1,
          "name": {
            "pl": "KODOMO",
            "uk": "Кодомо",
            "en": "Kodomo"
          },
          "description": {
            "pl": "Nuggetsy z kurczaka lub ryby, hosomaki z ogórkiem i awokado, chipsy krewetkowe, owoc, baby marchewka",
            "uk": "Нагетси з курки або риби, хосомакі з огірком і авокадо, креветкові чипси, фрукт, бейбі-морква.",
            "en": "Chicken or fish nuggets, cucumber and avocado hosomaki, prawn crackers, fruit and baby carrot."
          },
          "price": "33 zł",
          "image": "umami/goorder/107-kodomo.jpg",
          "source_image": "https://d3ul8m5cv419qn.cloudfront.net/images/3902/product/49c173ae-8c7c-4b82-b851-3577b9f14479",
          "is_bestseller": false
        }
      ]
    }
  ],
  "gallery": [
    {
      "title": {
        "pl": "Galeria 1",
        "uk": "Галерея 1",
        "en": "Gallery 1"
      },
      "alt": {
        "pl": "Galeria 1",
        "uk": "Галерея 1",
        "en": "Gallery 1"
      },
      "image": "umami/res1.png",
      "sort_order": 1
    },
    {
      "title": {
        "pl": "Galeria 2",
        "uk": "Галерея 2",
        "en": "Gallery 2"
      },
      "alt": {
        "pl": "Galeria 2",
        "uk": "Галерея 2",
        "en": "Gallery 2"
      },
      "image": "umami/res8.png",
      "sort_order": 2
    },
    {
      "title": {
        "pl": "Galeria 3",
        "uk": "Галерея 3",
        "en": "Gallery 3"
      },
      "alt": {
        "pl": "Galeria 3",
        "uk": "Галерея 3",
        "en": "Gallery 3"
      },
      "image": "umami/res3.png",
      "sort_order": 3
    },
    {
      "title": {
        "pl": "Galeria 4",
        "uk": "Галерея 4",
        "en": "Gallery 4"
      },
      "alt": {
        "pl": "Galeria 4",
        "uk": "Галерея 4",
        "en": "Gallery 4"
      },
      "image": "umami/res10.png",
      "sort_order": 4
    },
    {
      "title": {
        "pl": "Galeria 5",
        "uk": "Галерея 5",
        "en": "Gallery 5"
      },
      "alt": {
        "pl": "Galeria 5",
        "uk": "Галерея 5",
        "en": "Gallery 5"
      },
      "image": "umami/res5.png",
      "sort_order": 5
    },
    {
      "title": {
        "pl": "Galeria 6",
        "uk": "Галерея 6",
        "en": "Gallery 6"
      },
      "alt": {
        "pl": "Galeria 6",
        "uk": "Галерея 6",
        "en": "Gallery 6"
      },
      "image": "umami/res6.png",
      "sort_order": 6
    },
    {
      "title": {
        "pl": "Galeria 7",
        "uk": "Галерея 7",
        "en": "Gallery 7"
      },
      "alt": {
        "pl": "Galeria 7",
        "uk": "Галерея 7",
        "en": "Gallery 7"
      },
      "image": "umami/res7.png",
      "sort_order": 7
    },
    {
      "title": {
        "pl": "Galeria 8",
        "uk": "Галерея 8",
        "en": "Gallery 8"
      },
      "alt": {
        "pl": "Galeria 8",
        "uk": "Галерея 8",
        "en": "Gallery 8"
      },
      "image": "umami/res2.png",
      "sort_order": 8
    },
    {
      "title": {
        "pl": "Galeria 9",
        "uk": "Галерея 9",
        "en": "Gallery 9"
      },
      "alt": {
        "pl": "Galeria 9",
        "uk": "Галерея 9",
        "en": "Gallery 9"
      },
      "image": "umami/res9.png",
      "sort_order": 9
    },
    {
      "title": {
        "pl": "Galeria 10",
        "uk": "Галерея 10",
        "en": "Gallery 10"
      },
      "alt": {
        "pl": "Galeria 10",
        "uk": "Галерея 10",
        "en": "Gallery 10"
      },
      "image": "umami/res4.png",
      "sort_order": 10
    },
    {
      "title": {
        "pl": "Galeria 11",
        "uk": "Галерея 11",
        "en": "Gallery 11"
      },
      "alt": {
        "pl": "Galeria 11",
        "uk": "Галерея 11",
        "en": "Gallery 11"
      },
      "image": "umami/res11.png",
      "sort_order": 11
    },
    {
      "title": {
        "pl": "Galeria 12",
        "uk": "Галерея 12",
        "en": "Gallery 12"
      },
      "alt": {
        "pl": "Galeria 12",
        "uk": "Галерея 12",
        "en": "Gallery 12"
      },
      "image": "umami/res12.png",
      "sort_order": 12
    },
    {
      "title": {
        "pl": "Galeria 13",
        "uk": "Галерея 13",
        "en": "Gallery 13"
      },
      "alt": {
        "pl": "Galeria 13",
        "uk": "Галерея 13",
        "en": "Gallery 13"
      },
      "image": "umami/res13.png",
      "sort_order": 13
    },
    {
      "title": {
        "pl": "Galeria 14",
        "uk": "Галерея 14",
        "en": "Gallery 14"
      },
      "alt": {
        "pl": "Galeria 14",
        "uk": "Галерея 14",
        "en": "Gallery 14"
      },
      "image": "umami/res14.png",
      "sort_order": 14
    },
    {
      "title": {
        "pl": "Galeria 15",
        "uk": "Галерея 15",
        "en": "Gallery 15"
      },
      "alt": {
        "pl": "Galeria 15",
        "uk": "Галерея 15",
        "en": "Gallery 15"
      },
      "image": "umami/res15.png",
      "sort_order": 15
    }
  ],
  "socialLinks": [
    {
      "label": "Instagram",
      "url": "https://www.instagram.com/umamisushi_torun/",
      "icon": "umami/ig3.png",
      "sort_order": 1
    },
    {
      "label": "Facebook",
      "url": "https://www.facebook.com/profile.php?id=61583427270850",
      "icon": "umami/facebook.png",
      "sort_order": 2
    }
  ]
}
JSON, true, flags: JSON_THROW_ON_ERROR);

        $data['settings'][] = [
            'group' => 'about',
            'key' => 'about_image',
            'label' => 'About image',
            'value' => 'umami/res8.png',
            'type' => 'image',
            'sort_order' => 11,
        ];

        foreach ($data['texts'] as $text) {
            SiteText::updateOrCreate(
                ['key' => $text['key']],
                [
                    'group' => $text['group'],
                    'label' => $text['label'],
                    'value' => $text['value'],
                    'type' => $text['type'],
                    'sort_order' => $text['sort_order'],
                ],
            );
        }

        foreach ($data['settings'] as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'label' => $setting['label'],
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'sort_order' => $setting['sort_order'],
                ],
            );
        }

        foreach ($data['categories'] as $categoryData) {
            $category = MenuCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'intro_text' => $this->categoryIntroText($categoryData),
                    'seo_text' => $this->categorySeoText($categoryData),
                    'sort_order' => $categoryData['sort_order'],
                    'is_active' => true,
                ],
            );

            $usedSlugs = [];
            foreach ($categoryData['items'] as $itemData) {
                $baseSlug = Str::slug($itemData['name']['pl'] ?? 'danie') ?: 'danie';
                $slug = $baseSlug;
                $suffix = 2;

                while (in_array($slug, $usedSlugs, true) || MenuItem::query()->where('slug', $slug)->where('menu_category_id', '!=', $category->id)->exists()) {
                    $slug = $baseSlug.'-'.$suffix;
                    $suffix++;
                }

                $usedSlugs[] = $slug;

                MenuItem::updateOrCreate(
                    [
                        'menu_category_id' => $category->id,
                        'sort_order' => $itemData['sort_order'],
                    ],
                    [
                        'name' => $itemData['name'],
                        'slug' => $slug,
                        'description' => $itemData['description'],
                        'marketing_description' => $this->itemMarketingDescription($itemData, $categoryData),
                        'price' => $itemData['price'],
                        'image' => $itemData['image'],
                        'source_image' => $itemData['source_image'],
                        'is_bestseller' => $itemData['is_bestseller'],
                        'is_active' => true,
                    ],
                );
            }
        }

        foreach ($data['gallery'] as $image) {
            GalleryImage::updateOrCreate(
                ['image' => $image['image']],
                [
                    'title' => $image['title'],
                    'alt' => $image['alt'],
                    'sort_order' => $image['sort_order'],
                    'is_active' => true,
                ],
            );
        }

        foreach ($data['socialLinks'] as $link) {
            SocialLink::updateOrCreate(
                ['label' => $link['label']],
                [
                    'url' => $link['url'],
                    'icon' => $link['icon'],
                    'sort_order' => $link['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function categoryIntroText(array $category): array
    {
        $profile = $this->categoryProfile($category);

        return [
            'pl' => $profile['intro']['pl'],
            'uk' => $profile['intro']['uk'],
            'en' => $profile['intro']['en'],
        ];
    }

    private function categorySeoText(array $category): array
    {
        $profile = $this->categoryProfile($category);
        $examples = collect($category['items'] ?? [])
            ->sortBy(fn (array $item) => $item['is_bestseller'] ? 0 : ($item['sort_order'] ?? 999))
            ->take(5)
            ->map(fn (array $item) => $item['name'])
            ->all();

        $plExamples = collect($examples)->pluck('pl')->filter()->implode(', ');
        $ukExamples = collect($examples)->pluck('uk')->filter()->implode(', ');
        $enExamples = collect($examples)->pluck('en')->filter()->implode(', ');

        return [
            'pl' => "{$profile['seo']['pl']} ".$this->categorySeoClosing($category, $plExamples, 'pl'),
            'uk' => "{$profile['seo']['uk']} ".$this->categorySeoClosing($category, $ukExamples, 'uk'),
            'en' => "{$profile['seo']['en']} ".$this->categorySeoClosing($category, $enExamples, 'en'),
        ];
    }

    private function categorySeoClosing(array $category, string $examples, string $locale): string
    {
        $slug = $category['slug'];

        $texts = [
            'przystawki-zimne' => [
                'pl' => "W tej sekcji warto zwrócić uwagę na {$examples}: dobrze budują apetyt, ale nie przytłaczają przed głównym zamówieniem. To dobry wybór, gdy planujesz sushi w Toruniu i chcesz zacząć od czegoś świeżego, pikantnego albo lekko morskiego.",
                'uk' => "У цьому розділі варто звернути увагу на {$examples}: вони добре відкривають апетит, але не перевантажують перед основним замовленням. Це вдалий вибір, якщо плануєте суші в Торуні й хочете почати з чогось свіжого, пікантного або легкого морського смаку.",
                'en' => "In this section, {$examples} are worth noticing: they open the appetite without overpowering the main order. It is a good start when planning sushi in Toruń and wanting something fresh, spicy or lightly sea-inspired.",
            ],
            'przystawki-ciep-e' => [
                'pl' => "{$examples} dobrze sprawdzają się do dzielenia przy stole, szczególnie kiedy zamówienie ma mieć więcej tekstur niż same rolki. Ciepłe przystawki dodają chrupkości, aromatu i restauracyjnego rytmu przed ramenem, sushi albo daniem głównym.",
                'uk' => "{$examples} добре підходять для спільного столу, особливо коли в замовленні хочеться більше текстур, ніж лише роли. Гарячі закуски додають хрусткість, аромат і ресторанний ритм перед раменом, суші або основною стравою.",
                'en' => "{$examples} work well for sharing, especially when the order should have more texture than rolls alone. Warm starters add crunch, aroma and restaurant rhythm before ramen, sushi or a main dish.",
            ],
            'zupy' => [
                'pl' => "Wśród zup znajdziesz {$examples}, czyli propozycje na lżejszy start albo rozgrzewającą część posiłku. To sekcja dla gości, którzy lubią aromatyczny bulion, kwaśno-pikantne nuty lub delikatniejsze smaki przed sushi.",
                'uk' => "Серед супів є {$examples} — варіанти для легкого старту або зігрівальної частини трапези. Це розділ для гостей, які люблять ароматний бульйон, кисло-гострі ноти або делікатніші смаки перед суші.",
                'en' => "Among the soups you will find {$examples}, suitable as a lighter start or a warming part of the meal. This section is for guests who enjoy aromatic broth, hot-sour notes or gentler flavors before sushi.",
            ],
            'rameny' => [
                'pl' => "{$examples} pokazują różne kierunki ramenu: od mięsnych i głębokich po łagodniejsze lub warzywne. To dobra kategoria, gdy chcesz zamówić ramen w Toruniu jako pełny obiad, a nie tylko dodatek do sushi.",
                'uk' => "{$examples} показують різні напрями рамену: від м’ясних і глибоких до м’якших або овочевих. Це добра категорія, коли хочеться замовити рамен у Торуні як повноцінний обід, а не лише додаток до суші.",
                'en' => "{$examples} show different ramen directions: from meaty and deep to gentler or vegetable-forward. This is a strong category when you want to order ramen in Toruń as a full meal, not just an addition to sushi.",
            ],
            'dania-g-owne' => [
                'pl' => "Pozycje takie jak {$examples} są dobrym wyborem, gdy apetyt idzie bardziej w stronę makaronu, ryżu i ciepłego sosu niż klasycznych rolek. To część menu dla osób, które szukają azjatyckiego obiadu w Toruniu z konkretną porcją.",
                'uk' => "Такі позиції, як {$examples}, добре підходять, коли хочеться більше локшини, рису й теплого соусу, ніж класичних ролів. Це частина меню для тих, хто шукає азійський обід у Торуні з повноцінною порцією.",
                'en' => "Items such as {$examples} are a good choice when the appetite leans more toward noodles, rice and warm sauce than classic rolls. This part of the menu is for guests looking for an Asian lunch in Toruń with a proper portion.",
            ],
            'sashimi' => [
                'pl' => "{$examples} pozwalają skupić się na smaku ryby bez ryżu, panierki i mocnych sosów. To spokojna, elegancka kategoria dla osób, które cenią prostotę, świeżość i czysty charakter sushi.",
                'uk' => "{$examples} дозволяють зосередитися на смаку риби без рису, панірування й сильних соусів. Це спокійна, елегантна категорія для тих, хто цінує простоту, свіжість і чистий характер суші.",
                'en' => "{$examples} let the fish flavor stand on its own, without rice, coating or strong sauces. It is a calm, elegant category for guests who value simplicity, freshness and a clean sushi character.",
            ],
            'nigiri' => [
                'pl' => "Wybierając {$examples}, łatwo porównać różne ryby w tej samej, klasycznej formie. Nigiri dobrze sprawdza się jako elegancki dodatek do zestawu albo minimalistyczne sushi na spokojną kolację.",
                'uk' => "Обираючи {$examples}, легко порівняти різну рибу в одній класичній формі. Нігірі добре працює як елегантний додаток до сету або мінімалістичні суші для спокійної вечері.",
                'en' => "Choosing {$examples} makes it easy to compare different fish in the same classic form. Nigiri works well as an elegant addition to a set or as minimal sushi for a calm dinner.",
            ],
            'hosomaki' => [
                'pl' => "{$examples} są proste, czytelne i wygodne, kiedy chcesz dobrać kilka małych rolek do większego zamówienia. Hosomaki dobrze porządkują menu: jeden główny smak, ryż, nori i bez zbędnego ciężaru.",
                'uk' => "{$examples} прості, зрозумілі й зручні, коли хочеться додати кілька малих ролів до більшого замовлення. Хосомакі добре впорядковують меню: один головний смак, рис, норі й без зайвої важкості.",
                'en' => "{$examples} are simple, clear and convenient when adding several small rolls to a larger order. Hosomaki keep the menu tidy: one main flavor, rice, nori and no unnecessary heaviness.",
            ],
            'gunkanmaki' => [
                'pl' => "{$examples} są małe, ale intensywne, dlatego dobrze uzupełniają klasyczne rolki i nigiri. To kategoria dla osób, które lubią wyraziste nadzienia, tatar, krewetki albo węgorza podane w kilku konkretnych kęsach.",
                'uk' => "{$examples} невеликі, але насичені, тому добре доповнюють класичні роли й нігірі. Це категорія для тих, хто любить виразні начинки, тартар, креветки або вугра в кількох конкретних шматках.",
                'en' => "{$examples} are small but intense, so they complement classic rolls and nigiri well. This category is for guests who enjoy expressive fillings, tartare, shrimp or eel in a few focused bites.",
            ],
            'futomaki' => [
                'pl' => "{$examples} dają pełniejszy przekrój sushi i lepiej sycą niż małe rolki. Futomaki są dobrym wyborem, gdy chcesz wyraźny środek, kilka tekstur i klasyczną rolkę w większym formacie.",
                'uk' => "{$examples} дають повніший зріз суші й краще насичують, ніж малі роли. Футомакі — добрий вибір, коли хочеться виразної начинки, кількох текстур і класичного ролу в більшому форматі.",
                'en' => "{$examples} offer a fuller sushi cross-section and feel more filling than small rolls. Futomaki are a good choice when you want a clear filling, several textures and a classic roll in a larger format.",
            ],
            'rolki-smazone' => [
                'pl' => "{$examples} są bardziej komfortowe i intensywne niż lekkie rolki, bo dochodzi chrupkość oraz ciepły charakter. To dobra sekcja dla osób, które lubią sushi w bardziej wyrazistej, przyjemnie sycącej odsłonie.",
                'uk' => "{$examples} більш комфортні й насичені, ніж легкі роли, бо мають хрусткість і теплий характер. Це добрий розділ для тих, хто любить суші в більш виразному, приємно ситному форматі.",
                'en' => "{$examples} feel more comforting and intense than light rolls because of their crunch and warm character. This is a good section for guests who enjoy sushi in a bolder, satisfyingly filling form.",
            ],
            'freshmaki' => [
                'pl' => "{$examples} stawiają na świeżość i lżejszy odbiór, dlatego dobrze pasują do osób, które nie chcą panierki ani bardzo ciężkich sosów. To rolki na spokojne zamówienie, w którym liczy się delikatność i czysty smak.",
                'uk' => "{$examples} роблять акцент на свіжості й легшому відчутті, тому добре підходять тим, хто не хоче панірування або дуже важких соусів. Це роли для спокійного замовлення, де важлива делікатність і чистий смак.",
                'en' => "{$examples} focus on freshness and a lighter feel, making them suitable for guests who do not want coating or very heavy sauces. These rolls are for calmer orders where delicacy and clean flavor matter.",
            ],
            'uramaki' => [
                'pl' => "{$examples} pokazują nowoczesną stronę sushi: ryż na zewnątrz, efektowne dodatki i smaki, które łatwo polubić. To jedna z najlepszych kategorii, gdy zamawiasz sushi na wynos w Toruniu i chcesz różnorodności.",
                'uk' => "{$examples} показують сучасну сторону суші: рис зовні, ефектні додатки та смаки, які легко полюбити. Це одна з найкращих категорій, коли замовляєте суші з собою в Торуні й хочете різноманіття.",
                'en' => "{$examples} show the modern side of sushi: rice on the outside, appealing toppings and flavors that are easy to enjoy. It is one of the best categories when ordering sushi takeaway in Toruń and wanting variety.",
            ],
            'zestawy-sushi' => [
                'pl' => "{$examples} pomagają zamówić sushi bez długiego wybierania pojedynczych rolek. To praktyczna kategoria na spotkanie, rodzinny wieczór albo kolację dla kilku osób, kiedy na stole ma pojawić się różnorodność i wygodny podział porcji.",
                'uk' => "{$examples} допомагають замовити суші без довгого вибору окремих ролів. Це практична категорія для зустрічі, сімейного вечора або вечері на кілька людей, коли на столі потрібне різноманіття й зручний поділ порцій.",
                'en' => "{$examples} make it easier to order sushi without choosing every roll separately. It is a practical category for a meeting, family evening or dinner for several people, when variety and easy sharing matter.",
            ],
            'dodatki' => [
                'pl' => "{$examples} nie grają pierwszej roli, ale potrafią zmienić całe zamówienie: dodać ostrości, słodyczy, świeżości albo klasycznego sushi-akcentu. Warto dobrać je od razu, zwłaszcza przy większych zestawach na wynos.",
                'uk' => "{$examples} не головні в замовленні, але можуть змінити його настрій: додати гостроти, солодкуватості, свіжості або класичного суші-акценту. Їх варто додати одразу, особливо до більших сетів з собою.",
                'en' => "{$examples} are not the main part of the order, but they can change its mood: adding heat, sweetness, freshness or a classic sushi accent. They are worth adding right away, especially with larger takeaway sets.",
            ],
            'napoje' => [
                'pl' => "{$examples} pomagają odświeżyć smak między kęsami i dobrze pasują do tempury, ramenu oraz pikantniejszych dodatków. To prosta, ale potrzebna część zamówienia, szczególnie przy sushi na wynos.",
                'uk' => "{$examples} допомагають освіжити смак між шматками й добре пасують до темпури, рамену та пікантніших додатків. Це проста, але потрібна частина замовлення, особливо для суші з собою.",
                'en' => "{$examples} help refresh the palate between bites and pair well with tempura, ramen and spicier additions. It is a simple but useful part of the order, especially with sushi takeaway.",
            ],
            'menu-sezonowe' => [
                'pl' => "{$examples} warto sprawdzić wtedy, gdy chcesz spróbować czegoś spoza stałej karty. Menu sezonowe jest krótsze, bardziej zmienne i dobre dla gości, którzy lubią wracać po nowe smaki.",
                'uk' => "{$examples} варто переглянути, коли хочеться спробувати щось поза постійним меню. Сезонне меню коротше, змінніше й добре підходить гостям, які люблять повертатися за новими смаками.",
                'en' => "{$examples} are worth checking when you want something outside the regular menu. The seasonal menu is shorter, more changeable and good for guests who like returning for new flavors.",
            ],
            'dania-dla-dzieci' => [
                'pl' => "{$examples} ułatwia rodzinne zamówienie, kiedy najmłodsi potrzebują prostszego, łagodniejszego smaku. To spokojniejsza część menu, która pozwala dorosłym zamówić sushi, a dzieciom dostać coś wygodnego dla siebie.",
                'uk' => "{$examples} полегшує сімейне замовлення, коли наймолодшим потрібен простіший і м’якший смак. Це спокійніша частина меню, яка дозволяє дорослим замовити суші, а дітям отримати щось зручне для себе.",
                'en' => "{$examples} makes family ordering easier when younger guests need a simpler, milder flavor. It is a calmer part of the menu that lets adults order sushi while kids get something comfortable for themselves.",
            ],
        ];

        return $texts[$slug][$locale] ?? [
            'pl' => "W tej kategorii znajdziesz {$examples}, czyli propozycje przygotowane z myślą o wygodnym wyborze i świeżym smaku. To dobra część menu dla osób szukających sushi i kuchni azjatyckiej w Toruniu.",
            'uk' => "У цій категорії є {$examples} — позиції, створені для зручного вибору й свіжого смаку. Це добрий розділ меню для тих, хто шукає суші та азійську кухню в Торуні.",
            'en' => "In this category you will find {$examples}, prepared for easy choice and fresh flavor. It is a good part of the menu for guests looking for sushi and Asian food in Toruń.",
        ][$locale];
    }

    private function categoryTextName(array $category): array
    {
        $names = [
            'przystawki-zimne' => ['pl' => 'zimne przystawki', 'uk' => 'холодні закуски', 'en' => 'cold starters'],
            'przystawki-ciep-e' => ['pl' => 'ciepłe przystawki', 'uk' => 'гарячі закуски', 'en' => 'warm starters'],
            'zupy' => ['pl' => 'zupy azjatyckie', 'uk' => 'азійські супи', 'en' => 'Asian soups'],
            'rameny' => ['pl' => 'rameny', 'uk' => 'рамен', 'en' => 'ramen'],
            'dania-g-owne' => ['pl' => 'dania główne', 'uk' => 'основні страви', 'en' => 'main dishes'],
            'sashimi' => ['pl' => 'sashimi', 'uk' => 'сашимі', 'en' => 'sashimi'],
            'nigiri' => ['pl' => 'nigiri', 'uk' => 'нігірі', 'en' => 'nigiri'],
            'hosomaki' => ['pl' => 'hosomaki', 'uk' => 'хосомакі', 'en' => 'hosomaki'],
            'gunkanmaki' => ['pl' => 'gunkanmaki', 'uk' => 'гунканмакі', 'en' => 'gunkanmaki'],
            'futomaki' => ['pl' => 'futomaki', 'uk' => 'футомакі', 'en' => 'futomaki'],
            'rolki-smazone' => ['pl' => 'rolki smażone', 'uk' => 'смажені роли', 'en' => 'fried rolls'],
            'freshmaki' => ['pl' => 'freshmaki', 'uk' => 'фрешмакі', 'en' => 'freshmaki'],
            'uramaki' => ['pl' => 'uramaki', 'uk' => 'урамакі', 'en' => 'uramaki'],
            'zestawy-sushi' => ['pl' => 'zestawy sushi', 'uk' => 'суші-сети', 'en' => 'sushi sets'],
            'dodatki' => ['pl' => 'dodatki do sushi', 'uk' => 'додатки до суші', 'en' => 'sushi add-ons'],
            'napoje' => ['pl' => 'napoje', 'uk' => 'напої', 'en' => 'drinks'],
            'menu-sezonowe' => ['pl' => 'menu sezonowe', 'uk' => 'сезонне меню', 'en' => 'seasonal menu'],
            'dania-dla-dzieci' => ['pl' => 'dania dla dzieci', 'uk' => 'дитячі страви', 'en' => 'kids dishes'],
        ];

        if (isset($names[$category['slug']])) {
            return $names[$category['slug']];
        }

        return [
            'pl' => mb_strtolower($category['name']['pl'] ?? ''),
            'uk' => mb_strtolower($category['name']['uk'] ?? ''),
            'en' => mb_strtolower($category['name']['en'] ?? ''),
        ];
    }

    private function itemMarketingDescription(array $item, array $category): array
    {
        $name = $item['name'];
        $categoryName = $this->categoryTextName($category);
        $focus = $this->itemFocus($item, $category);
        $focus = [
            'pl' => $this->sentence($focus['pl']),
            'uk' => $this->sentence($focus['uk']),
            'en' => $this->sentence($focus['en']),
        ];
        $variant = ((int) ($item['sort_order'] ?? 1)) % 5;

        $texts = [
            [
                'pl' => "{$name['pl']} ma charakter, który od razu kojarzy się z dobrze dopracowaną kuchnią azjatycką: {$focus['pl']} To dobra propozycja, gdy chcesz zamówić {$categoryName['pl']} w Toruniu i zależy Ci na smaku, który zostaje w pamięci.",
                'uk' => "{$name['uk']} має характер, який одразу асоціюється з продуманою азійською кухнею: {$focus['uk']} Це вдалий вибір, якщо хочете замовити {$categoryName['uk']} у Торуні й цінуєте смак, який запам’ятовується.",
                'en' => "{$name['en']} has the character of carefully prepared Asian cuisine: {$focus['en']} It is a good choice when you want to order {$categoryName['en']} in Toruń and prefer a flavor that stays memorable.",
            ],
            [
                'pl' => "W {$name['pl']} najważniejsze jest pierwsze wrażenie: {$focus['pl']} Danie dobrze pasuje do spokojnego obiadu, wieczornego zamówienia online albo odbioru na wynos z Umami Sushi & Food.",
                'uk' => "У {$name['uk']} найважливіше перше враження: {$focus['uk']} Страва добре підходить для обіду, вечірнього онлайн-замовлення або самовивозу з Umami Sushi & Food.",
                'en' => "With {$name['en']}, the first impression matters most: {$focus['en']} It fits a relaxed lunch, an evening online order or takeaway pickup from Umami Sushi & Food.",
            ],
            [
                'pl' => "{$name['pl']} jest dla osób, które lubią zamawiać świadomie, a nie wybierać przypadkowo. {$focus['pl']} Sprawdza się jako samodzielne danie, część większego zamówienia lub element kolacji z sushi i kuchnią azjatycką w Toruniu.",
                'uk' => "{$name['uk']} — для тих, хто любить обирати усвідомлено, а не випадково. {$focus['uk']} Добре працює як окрема страва, частина більшого замовлення або елемент вечері з суші та азійською кухнею в Торуні.",
                'en' => "{$name['en']} is for guests who like to choose consciously rather than randomly. {$focus['en']} It works as a standalone dish, part of a larger order or one element of a sushi and Asian food dinner in Toruń.",
            ],
            [
                'pl' => "Jeśli porównujesz menu i chcesz wybrać coś z kategorii {$categoryName['pl']}, {$name['pl']} daje czytelny kierunek smaku. {$focus['pl']} To wygodna opcja do zamówienia online i odbioru w restauracji.",
                'uk' => "Якщо ви порівнюєте меню й хочете обрати щось із категорії {$categoryName['uk']}, {$name['uk']} дає зрозумілий напрям смаку. {$focus['uk']} Це зручний варіант для онлайн-замовлення та самовивозу.",
                'en' => "If you are comparing the menu and choosing from {$categoryName['en']}, {$name['en']} gives a clear flavor direction. {$focus['en']} It is a convenient option for online ordering and restaurant pickup.",
            ],
            [
                'pl' => "{$name['pl']} dobrze pokazuje styl Umami Sushi & Food: uważne przygotowanie, świeże składniki i smak bez przypadkowych dodatków. {$focus['pl']} To wybór dla fanów kuchni azjatyckiej w Toruniu.",
                'uk' => "{$name['uk']} добре показує стиль Umami Sushi & Food: уважне приготування, свіжі інгредієнти й смак без зайвих випадкових додатків. {$focus['uk']} Це вибір для шанувальників азійської кухні в Торуні.",
                'en' => "{$name['en']} shows the Umami Sushi & Food style well: careful preparation, fresh ingredients and flavor without unnecessary extras. {$focus['en']} It is a choice for fans of Asian food in Toruń.",
            ],
        ];

        return $texts[$variant];
    }

    private function sentence(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            return '';
        }

        return mb_strtoupper(mb_substr($text, 0, 1)).mb_substr($text, 1);
    }

    private function itemFocus(array $item, array $category): array
    {
        $slug = $item['slug'] ?? Str::slug($item['name']['pl'] ?? '');
        $name = mb_strtolower($item['name']['pl'] ?? '');

        $exact = [
            'chashu-ramen' => [
                'pl' => 'głęboki bulion, miękkie mięso i marynowane jajko tworzą miskę, po którą wraca się wtedy, gdy potrzebny jest prawdziwie sycący ramen.',
                'uk' => 'глибокий бульйон, м’яке м’ясо та мариноване яйце створюють миску, до якої хочеться повертатися, коли потрібен по-справжньому ситний рамен.',
                'en' => 'deep broth, tender meat and marinated egg create a bowl worth returning to when you want truly satisfying ramen.',
            ],
            'bifu-ramen' => [
                'pl' => 'kremowa baza i wołowina dają mocniejszy, bardziej treściwy charakter niż w lekkich bulionach.',
                'uk' => 'кремова основа й яловичина дають сильніший, більш насичений характер, ніж у легких бульйонах.',
                'en' => 'a creamy base and beef give it a stronger, fuller character than lighter broths.',
            ],
            'tom-yum' => [
                'pl' => 'ostro-kwaśny aromat, cytrusowa świeżość i morski akcent sprawiają, że zupa jest intensywna już od pierwszej łyżki.',
                'uk' => 'гостро-кислий аромат, цитрусова свіжість і морський акцент роблять суп насиченим уже з першої ложки.',
                'en' => 'hot-sour aroma, citrus freshness and a sea-inspired accent make the soup intense from the first spoonful.',
            ],
            'master-roll' => [
                'pl' => 'duży wybór rolek pozwala spróbować kilku smaków bez układania całego zamówienia od zera.',
                'uk' => 'великий вибір ролів дозволяє спробувати кілька смаків без складання всього замовлення з нуля.',
                'en' => 'a broad selection of rolls lets you try several flavors without building the whole order from scratch.',
            ],
            'deluxe' => [
                'pl' => 'to zestaw na większy apetyt i dłuższy wieczór, kiedy na stole ma pojawić się kilka charakterów sushi naraz.',
                'uk' => 'це сет для більшого апетиту й довшого вечора, коли на столі має з’явитися кілька характерів суші одразу.',
                'en' => 'it is a set for a bigger appetite and a longer evening, when several sushi characters should land on the table at once.',
            ],
            'umami' => [
                'pl' => 'zestaw pokazuje restauracyjny styl w pigułce: różne rolki, różne tekstury i wygodny wybór dla kilku osób.',
                'uk' => 'сет коротко показує стиль ресторану: різні роли, різні текстури й зручний вибір для кількох людей.',
                'en' => 'the set captures the restaurant style in one order: different rolls, different textures and an easy choice for several people.',
            ],
        ];

        if (isset($exact[$slug])) {
            return $exact[$slug];
        }

        $rules = [
            ['needles' => ['sashimi'], 'text' => ['pl' => 'czysty smak ryby i prostota podania pozwalają skupić się na jakości bez ciężkich dodatków.', 'uk' => 'чистий смак риби та простота подачі дозволяють зосередитися на якості без важких додатків.', 'en' => 'clean fish flavor and simple serving let the quality stand out without heavy extras.']],
            ['needles' => ['nigiri'], 'text' => ['pl' => 'proporcja ryżu i głównego dodatku daje klasyczne sushi w minimalistycznej, eleganckiej formie.', 'uk' => 'пропорція рису й головного додатку дає класичні суші в мінімалістичній, елегантній формі.', 'en' => 'the proportion of rice and topping gives classic sushi in a minimal, elegant form.']],
            ['needles' => ['tempura', 'panko', 'smazona', 'smażona'], 'text' => ['pl' => 'chrupiąca tekstura dodaje energii i sprawia, że każdy kęs jest bardziej wyrazisty.', 'uk' => 'хрустка текстура додає енергії й робить кожен шматок виразнішим.', 'en' => 'crispy texture adds energy and makes every bite more expressive.']],
            ['needles' => ['uramaki', 'uramak'], 'text' => ['pl' => 'ryż na zewnątrz, efektowny przekrój i dopracowane dodatki dają nowoczesny styl sushi.', 'uk' => 'рис зовні, виразний зріз і продумані додатки створюють сучасний стиль суші.', 'en' => 'rice on the outside, an appealing cut and thoughtful toppings create a modern sushi style.']],
            ['needles' => ['futomak'], 'text' => ['pl' => 'większa rolka daje pełniejszy kęs i pozwala połączyć kilka smaków w jednej porcji.', 'uk' => 'більший рол дає повніший шматок і дозволяє поєднати кілька смаків в одній порції.', 'en' => 'a larger roll gives a fuller bite and combines several flavors in one portion.']],
            ['needles' => ['hosomak'], 'text' => ['pl' => 'prosta rolka stawia na jeden wyraźny kierunek smaku i dobrze przygotowany ryż.', 'uk' => 'простий рол робить акцент на одному чіткому напрямі смаку й добре підготовленому рисі.', 'en' => 'the simple roll focuses on one clear flavor direction and well-prepared rice.']],
            ['needles' => ['gunkanmaki'], 'text' => ['pl' => 'mała forma koncentruje intensywny smak, dlatego świetnie uzupełnia klasyczne rolki.', 'uk' => 'невелика форма концентрує насичений смак, тому чудово доповнює класичні роли.', 'en' => 'the small form concentrates bold flavor, making it a great complement to classic rolls.']],
            ['needles' => ['tatar'], 'text' => ['pl' => 'aksamitna struktura i intensywny rybny charakter tworzą propozycję dla fanów mocniejszego sushi.', 'uk' => 'оксамитова текстура й насичений рибний характер створюють вибір для прихильників виразніших суші.', 'en' => 'silky texture and a bold fish character make it a choice for fans of stronger sushi flavors.']],
            ['needles' => ['tataki', 'aburi'], 'text' => ['pl' => 'delikatne opalanie wydobywa aromat i dodaje głębi bez odbierania świeżości.', 'uk' => 'легке обпалювання розкриває аромат і додає глибини, не забираючи свіжості.', 'en' => 'gentle searing brings out aroma and depth without taking away freshness.']],
            ['needles' => ['ramen'], 'text' => ['pl' => 'gorący bulion, makaron i dodatki budują pełną miskę na sycący obiad lub wieczór.', 'uk' => 'гарячий бульйон, локшина та додатки створюють повну миску для ситного обіду або вечора.', 'en' => 'hot broth, noodles and toppings build a complete bowl for a filling lunch or evening meal.']],
            ['needles' => ['udon', 'soba'], 'text' => ['pl' => 'sprężysty makaron i intensywny sos tworzą ciepłe danie dla osób, które wolą pełny posiłek zamiast rolek.', 'uk' => 'пружна локшина й насичений соус створюють гарячу страву для тих, хто хоче повноцінну порцію замість ролів.', 'en' => 'springy noodles and an expressive sauce create a warm dish for guests who prefer a full meal over rolls.']],
            ['needles' => ['gyoza'], 'text' => ['pl' => 'małe pierożki mają komfortowy, ciepły charakter i dobrze zaczynają większe zamówienie.', 'uk' => 'невеликі пельмені мають теплий комфортний характер і добре відкривають більше замовлення.', 'en' => 'small dumplings bring warm comfort and make a good start to a larger order.']],
            ['needles' => ['harumaki'], 'text' => ['pl' => 'lekka chrupkość i ciepły środek sprawiają, że to przystawka dobra do dzielenia.', 'uk' => 'легка хрусткість і теплий центр роблять цю закуску доброю для спільного столу.', 'en' => 'light crispness and a warm center make it a starter that is easy to share.']],
            ['needles' => ['edamame'], 'text' => ['pl' => 'lekko słony, zielony akcent dobrze otwiera posiłek i nie przytłacza kolejnych dań.', 'uk' => 'легкий солонуватий зелений акцент добре відкриває трапезу й не перебиває наступні страви.', 'en' => 'a lightly salty green accent opens the meal without overpowering the dishes that follow.']],
            ['needles' => ['kimchi'], 'text' => ['pl' => 'pikantno-kwaśny charakter dodaje zamówieniu energii i dobrze przełamuje ryż oraz smażone dodatki.', 'uk' => 'гостро-кислий характер додає замовленню енергії й добре врівноважує рис та смажені додатки.', 'en' => 'spicy-tangy character adds energy to the order and balances rice and fried additions well.']],
            ['needles' => ['wakame'], 'text' => ['pl' => 'morska świeżość i lekko słodki profil wprowadzają spokojny kontrast do bardziej intensywnych dań.', 'uk' => 'морська свіжість і легкий солодкуватий профіль створюють спокійний контраст до більш насичених страв.', 'en' => 'sea freshness and a gently sweet profile create a calm contrast to more intense dishes.']],
            ['needles' => ['sos', 'wasabi', 'imbir'], 'text' => ['pl' => 'dodatek pozwala dopasować intensywność zamówienia i podkreślić ulubiony kierunek smaku.', 'uk' => 'додаток допомагає налаштувати інтенсивність замовлення й підкреслити улюблений напрям смаку.', 'en' => 'the add-on helps adjust the intensity of the order and highlight your preferred flavor direction.']],
            ['needles' => ['pepsi', 'lemoniada'], 'text' => ['pl' => 'napój odświeża podniebienie i dobrze równoważy intensywne sosy, tempurę oraz ramen.', 'uk' => 'напій освіжає смак і добре врівноважує інтенсивні соуси, темпуру та рамен.', 'en' => 'the drink refreshes the palate and balances intense sauces, tempura and ramen.']],
            ['needles' => ['vege', 'yasai', 'tofu', 'warzywa', 'szparag'], 'text' => ['pl' => 'roślinny profil daje świeżość, lekkość i dobrą alternatywę dla rybnych oraz mięsnych pozycji.', 'uk' => 'рослинний профіль дає свіжість, легкість і добру альтернативу рибним та м’ясним позиціям.', 'en' => 'the plant-forward profile brings freshness, lightness and a good alternative to fish and meat options.']],
            ['needles' => ['losos', 'łosoś', 'sake'], 'text' => ['pl' => 'łosoś daje miękkość, delikatną tłustość i przyjemnie łagodny charakter sushi.', 'uk' => 'лосось дає м’якість, делікатну жирність і приємно ніжний характер суші.', 'en' => 'salmon brings softness, gentle richness and a pleasantly mild sushi character.']],
            ['needles' => ['tunczyk', 'tuńczyk', 'maguro', 'tekka'], 'text' => ['pl' => 'tuńczyk wnosi czystszy, bardziej zdecydowany smak dla osób lubiących wyraźniejszą rybę.', 'uk' => 'тунець дає чистіший і рішучіший смак для тих, хто любить виразнішу рибу.', 'en' => 'tuna brings a cleaner, more decisive flavor for guests who enjoy more expressive fish.']],
            ['needles' => ['ebi', 'krewet'], 'text' => ['pl' => 'krewetka dodaje słodyczy, sprężystej tekstury i eleganckiego morskiego akcentu.', 'uk' => 'креветка додає солодкуватість, пружну текстуру й елегантний морський акцент.', 'en' => 'shrimp adds sweetness, springy texture and an elegant sea-inspired accent.']],
            ['needles' => ['unagi', 'wegorz', 'węgorz'], 'text' => ['pl' => 'węgorz daje głębszy, lekko dymny charakter i przyjemnie otula ryż.', 'uk' => 'вугор дає глибший, злегка димний характер і приємно огортає рис.', 'en' => 'eel brings a deeper, lightly smoky character and wraps the rice in rich flavor.']],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['needles'] as $needle) {
                if (str_contains($slug, $needle) || str_contains($name, $needle)) {
                    return $rule['text'];
                }
            }
        }

        return $this->categoryProfile($category)['item'];
    }

    private function categoryProfile(array $category): array
    {
        $slug = $category['slug'];

        $profiles = [
            'przystawki-zimne' => [
                'intro' => [
                    'pl' => 'Zimne przystawki są dobrym początkiem posiłku: lekkie, wyraziste i gotowe, żeby pobudzić apetyt przed sushi, ramenem albo zestawem rolek.',
                    'uk' => 'Холодні закуски добре відкривають трапезу: легкі, виразні й створені, щоб підготувати смак до суші, рамену або сету ролів.',
                    'en' => 'Cold starters are a clean way to begin the meal: light, expressive and ready to open the appetite before sushi, ramen or a roll set.',
                ],
                'seo' => [
                    'pl' => 'Przystawki zimne w Umami Sushi & Food są dla gości, którzy lubią zacząć od czegoś świeżego, kwaśnego, pikantnego albo lekko morskiego.',
                    'uk' => 'Холодні закуски в Umami Sushi & Food підходять гостям, які люблять почати з чогось свіжого, кислуватого, пікантного або легкого морського смаку.',
                    'en' => 'Cold starters at Umami Sushi & Food are for guests who like to begin with something fresh, tangy, spicy or gently sea-inspired.',
                ],
                'item' => [
                    'pl' => 'stawia na świeżość, kontrast temperatur i wyrazisty pierwszy akcent przed głównym daniem.',
                    'uk' => 'робить акцент на свіжості, контрасті температур і виразному першому смаку перед основною стравою.',
                    'en' => 'focuses on freshness, temperature contrast and a clear first accent before the main dish.',
                ],
            ],
            'przystawki-ciep-e' => [
                'intro' => [
                    'pl' => 'Ciepłe przystawki to chrupiące tempury, pierożki i małe dania, które najlepiej smakują od razu po przygotowaniu.',
                    'uk' => 'Гарячі закуски — це хрустка темпура, пельмені та невеликі страви, які найкраще смакують одразу після приготування.',
                    'en' => 'Warm starters bring crispy tempura, dumplings and small dishes that taste best right after preparation.',
                ],
                'seo' => [
                    'pl' => 'Ta część menu jest dobra, kiedy szukasz czegoś ciepłego do podziału, dodatku do sushi albo małej porcji przed ramenem.',
                    'uk' => 'Цей розділ меню доречний, коли хочеться чогось теплого для спільного столу, додатку до суші або невеликої порції перед раменом.',
                    'en' => 'This menu section is useful when you want something warm to share, an add-on to sushi or a small portion before ramen.',
                ],
                'item' => [
                    'pl' => 'łączy ciepło, chrupkość i aromat, dlatego dobrze pasuje jako dodatek do większego zamówienia.',
                    'uk' => 'поєднує тепло, хрусткість і аромат, тому добре підходить як додаток до більшого замовлення.',
                    'en' => 'combines warmth, crunch and aroma, so it works well as an addition to a larger order.',
                ],
            ],
            'zupy' => [
                'intro' => [
                    'pl' => 'Zupy w Umami to aromatyczna część menu: od lekkich bulionów po wyraźniejsze smaki z owocami morza, kurczakiem albo warzywami.',
                    'uk' => 'Супи в Umami — ароматна частина меню: від легких бульйонів до виразніших смаків із морепродуктами, куркою або овочами.',
                    'en' => 'Soups at Umami are an aromatic part of the menu, from light broths to bolder flavors with seafood, chicken or vegetables.',
                ],
                'seo' => [
                    'pl' => 'To dobry wybór dla osób, które szukają rozgrzewającej zupy azjatyckiej w Toruniu albo chcą dobrać coś lekkiego przed sushi.',
                    'uk' => 'Це добрий вибір для тих, хто шукає зігрівальний азійський суп у Торуні або хоче щось легке перед суші.',
                    'en' => 'This is a good choice for guests looking for a warming Asian soup in Toruń or something light before sushi.',
                ],
                'item' => [
                    'pl' => 'opiera się na aromacie bulionu i dodatkach, które budują pełniejszy, rozgrzewający smak.',
                    'uk' => 'тримається на ароматі бульйону та додатках, які створюють глибший, зігрівальний смак.',
                    'en' => 'is built around broth aroma and toppings that create a fuller, warming flavor.',
                ],
            ],
            'rameny' => [
                'intro' => [
                    'pl' => 'Rameny są sycącą częścią menu: gorący bulion, makaron, dodatki i smak, który sprawdza się szczególnie wtedy, gdy chcesz pełnego dania.',
                    'uk' => 'Рамени — ситна частина меню: гарячий бульйон, локшина, додатки й смак, який особливо доречний, коли хочеться повноцінної страви.',
                    'en' => 'Ramen is the hearty part of the menu: hot broth, noodles, toppings and a flavor that works when you want a complete dish.',
                ],
                'seo' => [
                    'pl' => 'Ramen w Toruniu często wybierają osoby, które szukają czegoś bardziej treściwego niż rolki, ale nadal chcą kuchni japońskiej i azjatyckiej.',
                    'uk' => 'Рамен у Торуні часто обирають ті, хто хоче чогось ситнішого за роли, але все ще в стилі японської та азійської кухні.',
                    'en' => 'Ramen in Toruń is often chosen by guests who want something more filling than rolls while staying close to Japanese and Asian cuisine.',
                ],
                'item' => [
                    'pl' => 'buduje smak na gorącym bulionie, makaronie i dodatkach, które dają pełną, sycącą miskę.',
                    'uk' => 'будує смак на гарячому бульйоні, локшині та додатках, що створюють повну й ситну миску.',
                    'en' => 'builds flavor through hot broth, noodles and toppings that create a full, satisfying bowl.',
                ],
            ],
            'dania-g-owne' => [
                'intro' => [
                    'pl' => 'Dania główne to wybór dla osób, które chcą pełnego posiłku z ryżem, makaronem, mięsem, warzywami albo intensywnym sosem.',
                    'uk' => 'Основні страви — вибір для тих, хто хоче повноцінну порцію з рисом, локшиною, м’ясом, овочами або насиченим соусом.',
                    'en' => 'Main dishes are for guests who want a full meal with rice, noodles, meat, vegetables or an expressive sauce.',
                ],
                'seo' => [
                    'pl' => 'Ta kategoria pomaga szybko znaleźć azjatycki obiad w Toruniu, kiedy sushi ma być dodatkiem albo kiedy masz ochotę na ciepłe danie.',
                    'uk' => 'Ця категорія допомагає швидко знайти азійський обід у Торуні, коли суші — лише додаток або коли хочеться гарячої страви.',
                    'en' => 'This category helps you quickly find an Asian lunch in Toruń when sushi is an add-on or when you want a warm dish.',
                ],
                'item' => [
                    'pl' => 'jest nastawione na pełną porcję, konkretny sos i składniki, które dobrze sprawdzają się jako samodzielny posiłek.',
                    'uk' => 'орієнтована на повну порцію, виразний соус і складники, що добре працюють як самостійна страва.',
                    'en' => 'is focused on a full portion, a clear sauce and ingredients that work as a standalone meal.',
                ],
            ],
            'sashimi' => [
                'intro' => [
                    'pl' => 'Sashimi pozwala skupić się na czystym smaku ryby, strukturze cięcia i prostocie bez ryżu oraz dodatków.',
                    'uk' => 'Сашимі дає змогу зосередитися на чистому смаку риби, текстурі нарізки та простоті без рису й зайвих додатків.',
                    'en' => 'Sashimi focuses on clean fish flavor, cut texture and simplicity without rice or unnecessary extras.',
                ],
                'seo' => [
                    'pl' => 'To kategoria dla osób, które dobrze znają sushi i chcą zamówić rybę w najprostszym, najbardziej bezpośrednim wydaniu.',
                    'uk' => 'Це категорія для тих, хто добре знає суші й хоче замовити рибу в найпростішому та найпрямішому форматі.',
                    'en' => 'This category is for guests who know sushi well and want fish in its simplest, most direct form.',
                ],
                'item' => [
                    'pl' => 'pozwala ocenić jakość ryby bez nadmiaru dodatków, sosów i ryżu.',
                    'uk' => 'дозволяє оцінити якість риби без надміру додатків, соусів і рису.',
                    'en' => 'lets the quality of the fish speak without too many toppings, sauces or rice.',
                ],
            ],
            'nigiri' => [
                'intro' => [
                    'pl' => 'Nigiri łączy porcję ryżu z wybranym dodatkiem na wierzchu, dlatego jest klasycznym sposobem na poznanie smaku ryby.',
                    'uk' => 'Нігірі поєднує порцію рису з обраним додатком зверху, тому це класичний спосіб відчути смак риби.',
                    'en' => 'Nigiri combines a portion of rice with a topping on top, making it a classic way to taste fish.',
                ],
                'seo' => [
                    'pl' => 'Nigiri w Toruniu to dobry wybór dla osób, które chcą sushi w minimalistycznej formie, bez ciężkich sosów i rozbudowanych rolek.',
                    'uk' => 'Нігірі в Торуні — добрий вибір для тих, хто хоче суші в мінімалістичній формі, без важких соусів і складних ролів.',
                    'en' => 'Nigiri in Toruń is a good choice for guests who want sushi in a minimal form, without heavy sauces or complex rolls.',
                ],
                'item' => [
                    'pl' => 'stawia na proporcję ryżu i głównego dodatku, dzięki czemu smak jest prosty i czytelny.',
                    'uk' => 'робить акцент на пропорції рису й основного додатку, тому смак простий і зрозумілий.',
                    'en' => 'focuses on the proportion of rice and topping, keeping the flavor simple and clear.',
                ],
            ],
            'hosomaki' => [
                'intro' => [
                    'pl' => 'Hosomaki to małe, proste rolki z jednym głównym składnikiem, dobre jako dodatek do większego zamówienia.',
                    'uk' => 'Хосомакі — невеликі прості роли з одним головним інгредієнтом, доречні як додаток до більшого замовлення.',
                    'en' => 'Hosomaki are small, simple rolls with one main ingredient, useful as an addition to a larger order.',
                ],
                'seo' => [
                    'pl' => 'Ta kategoria sprawdza się, gdy chcesz zamówić klasyczne sushi w Toruniu, bez wielu dodatków i ciężkich sosów.',
                    'uk' => 'Ця категорія підходить, коли хочеться замовити класичні суші в Торуні без багатьох додатків і важких соусів.',
                    'en' => 'This category works when you want to order classic sushi in Toruń without many toppings or heavy sauces.',
                ],
                'item' => [
                    'pl' => 'jest prostą rolką, w której najważniejszy jest jeden główny składnik i dobrze przygotowany ryż.',
                    'uk' => 'є простою ролкою, де головне — один основний інгредієнт і добре підготовлений рис.',
                    'en' => 'is a simple roll where one main ingredient and well-prepared rice matter most.',
                ],
            ],
            'gunkanmaki' => [
                'intro' => [
                    'pl' => 'Gunkanmaki to małe porcje z intensywnym nadzieniem, dobre dla osób, które lubią wyrazisty smak w kilku kęsach.',
                    'uk' => 'Гунканмакі — невеликі порції з насиченою начинкою, добрі для тих, хто любить виразний смак у кілька укусів.',
                    'en' => 'Gunkanmaki are small portions with expressive fillings, good for guests who enjoy bold flavor in a few bites.',
                ],
                'seo' => [
                    'pl' => 'To bardziej charakterystyczna część menu sushi, często wybierana przez osoby szukające tatara, węgorza albo krewetek w zwartej formie.',
                    'uk' => 'Це більш характерна частина суші-меню, яку часто обирають ті, хто шукає тартар, вугра або креветки в компактній формі.',
                    'en' => 'This is a more distinctive part of the sushi menu, often chosen by guests looking for tartare, eel or shrimp in a compact form.',
                ],
                'item' => [
                    'pl' => 'koncentruje dużo smaku w małej porcji, dlatego dobrze uzupełnia zestawy i klasyczne rolki.',
                    'uk' => 'концентрує багато смаку в невеликій порції, тому добре доповнює сети й класичні роли.',
                    'en' => 'concentrates a lot of flavor in a small portion, making it a good complement to sets and classic rolls.',
                ],
            ],
            'futomaki' => [
                'intro' => [
                    'pl' => 'Futomaki to większe rolki z bogatszym środkiem, dobre dla osób, które chcą pełniejszego smaku w jednej porcji.',
                    'uk' => 'Футомакі — більші роли з насиченішою начинкою, добрі для тих, хто хоче повніший смак в одній порції.',
                    'en' => 'Futomaki are larger rolls with richer fillings, good for guests who want fuller flavor in one portion.',
                ],
                'seo' => [
                    'pl' => 'Futomaki w Toruniu to jedna z najczęściej wybieranych kategorii, bo łączy ryż, rybę, warzywa i dodatki w czytelnej kompozycji.',
                    'uk' => 'Футомакі в Торуні — одна з найчастіше обраних категорій, бо поєднує рис, рибу, овочі та додатки в зрозумілій композиції.',
                    'en' => 'Futomaki in Toruń is one of the most popular categories because it combines rice, fish, vegetables and toppings in a clear composition.',
                ],
                'item' => [
                    'pl' => 'ma pełniejszy przekrój składników i dobrze sprawdza się, gdy chcesz bardziej sycącą rolkę.',
                    'uk' => 'має повніший склад інгредієнтів і добре підходить, коли хочеться ситнішого ролу.',
                    'en' => 'has a fuller set of ingredients and works well when you want a more filling roll.',
                ],
            ],
            'rolki-smazone' => [
                'intro' => [
                    'pl' => 'Rolki smażone są dla fanów chrupiącej tekstury, ciepłego środka i bardziej wyrazistego efektu niż w klasycznym sushi.',
                    'uk' => 'Смажені роли — для прихильників хрусткої текстури, теплого центру й виразнішого ефекту, ніж у класичних суші.',
                    'en' => 'Fried rolls are for fans of crispy texture, warm filling and a bolder effect than classic sushi.',
                ],
                'seo' => [
                    'pl' => 'Ta kategoria jest często wybierana przez osoby, które dopiero zaczynają przygodę z sushi albo lubią połączenie panierki, ryżu i sosu.',
                    'uk' => 'Цю категорію часто обирають ті, хто лише знайомиться із суші або любить поєднання панірування, рису та соусу.',
                    'en' => 'This category is often chosen by guests who are new to sushi or enjoy the combination of coating, rice and sauce.',
                ],
                'item' => [
                    'pl' => 'dodaje do sushi chrupkość i ciepło, dzięki czemu rolka ma bardziej komfortowy, intensywny charakter.',
                    'uk' => 'додає суші хрусткість і тепло, завдяки чому рол має більш комфортний і насичений характер.',
                    'en' => 'adds crunch and warmth to sushi, giving the roll a more comforting and intense character.',
                ],
            ],
            'freshmaki' => [
                'intro' => [
                    'pl' => 'Freshmaki są lekkie, świeże i delikatniejsze w odbiorze, dobre dla osób, które lubią czysty smak bez ciężkiej panierki.',
                    'uk' => 'Фрешмакі — легкі, свіжі й делікатніші за відчуттям, добрі для тих, хто любить чистий смак без важкого панірування.',
                    'en' => 'Freshmaki are light, fresh and gentler in character, good for guests who enjoy clean flavor without heavy coating.',
                ],
                'seo' => [
                    'pl' => 'To dobry wybór, gdy szukasz świeżych rolek sushi w Toruniu z rybą, warzywami i wyraźnie lżejszym profilem.',
                    'uk' => 'Це добрий вибір, коли шукаєте свіжі суші-роли в Торуні з рибою, овочами та помітно легшим профілем.',
                    'en' => 'This is a good choice when looking for fresh sushi rolls in Toruń with fish, vegetables and a clearly lighter profile.',
                ],
                'item' => [
                    'pl' => 'stawia na świeży charakter i lekkość, dlatego dobrze pasuje do spokojnego, mniej ciężkiego zamówienia.',
                    'uk' => 'робить акцент на свіжості й легкості, тому добре підходить для спокійного, менш важкого замовлення.',
                    'en' => 'focuses on freshness and lightness, making it suitable for a calmer, less heavy order.',
                ],
            ],
            'uramaki' => [
                'intro' => [
                    'pl' => 'Uramaki to popularne rolki z ryżem na zewnątrz, często wybierane za kremowe dodatki, rybę i efektowny wygląd.',
                    'uk' => 'Урамакі — популярні роли з рисом зовні, які часто обирають за кремові додатки, рибу та виразний вигляд.',
                    'en' => 'Uramaki are popular inside-out rolls, often chosen for creamy toppings, fish and an appealing look.',
                ],
                'seo' => [
                    'pl' => 'Uramaki w Toruniu są dobrym wyborem dla osób, które lubią nowoczesne sushi z awokado, serkiem, tatarem, opalaniem albo pikantnym sosem.',
                    'uk' => 'Урамакі в Торуні — добрий вибір для тих, хто любить сучасні суші з авокадо, сиром, тартаром, обпалюванням або пікантним соусом.',
                    'en' => 'Uramaki in Toruń are a good choice for guests who enjoy modern sushi with avocado, cream cheese, tartare, searing or spicy sauce.',
                ],
                'item' => [
                    'pl' => 'łączy ryż na zewnątrz z wyrazistym środkiem, dlatego rolka jest efektowna i łatwa do lubienia.',
                    'uk' => 'поєднує рис зовні з виразною начинкою, тому рол виглядає ефектно й легко подобається.',
                    'en' => 'combines rice on the outside with an expressive filling, making the roll appealing and easy to enjoy.',
                ],
            ],
            'zestawy-sushi' => [
                'intro' => [
                    'pl' => 'Zestawy sushi ułatwiają wybór, gdy zamawiasz dla dwóch osób, rodziny albo grupy i chcesz kilka smaków w jednym pudełku.',
                    'uk' => 'Суші-сети спрощують вибір, коли замовляєте для двох, родини або компанії й хочете кілька смаків в одному наборі.',
                    'en' => 'Sushi sets make ordering easier for two people, family or a group when you want several flavors in one box.',
                ],
                'seo' => [
                    'pl' => 'Zestawy sushi są najwygodniejszym wyborem, gdy na stole ma pojawić się kilka smaków naraz: dla pary, rodziny, spotkania ze znajomymi albo większego zamówienia na wynos.',
                    'uk' => 'Суші-сети — найзручніший вибір, коли на столі має бути кілька смаків одразу: для пари, родини, зустрічі з друзями або більшого замовлення з собою.',
                    'en' => 'Sushi sets are the easiest choice when several flavors should appear on the table at once: for a couple, family, friends or a larger takeaway order.',
                ],
                'item' => [
                    'pl' => 'zbiera kilka rolek w jednym zamówieniu, więc pozwala spróbować różnych smaków bez wybierania każdej pozycji osobno.',
                    'uk' => 'збирає кілька ролів в одному замовленні, тому дає змогу спробувати різні смаки без вибору кожної позиції окремо.',
                    'en' => 'brings several rolls into one order, making it possible to try different flavors without choosing every item separately.',
                ],
            ],
            'dodatki' => [
                'intro' => [
                    'pl' => 'Dodatki pomagają dopasować zamówienie do własnego smaku: więcej sosu, imbiru, wasabi albo mały element uzupełniający.',
                    'uk' => 'Додатки допомагають налаштувати замовлення під власний смак: більше соусу, імбиру, васабі або невеликий супровідний елемент.',
                    'en' => 'Add-ons help adjust the order to your taste: more sauce, ginger, wasabi or a small supporting element.',
                ],
                'seo' => [
                    'pl' => 'Ta sekcja jest praktyczna przy zamówieniach sushi na wynos, kiedy chcesz uzupełnić rolki o sosy i klasyczne dodatki.',
                    'uk' => 'Цей розділ практичний для замовлень суші з собою, коли хочеться доповнити роли соусами й класичними додатками.',
                    'en' => 'This section is practical for sushi takeaway orders when you want to complete rolls with sauces and classic add-ons.',
                ],
                'item' => [
                    'pl' => 'jest praktycznym uzupełnieniem zamówienia i pozwala dopasować intensywność smaku do własnych preferencji.',
                    'uk' => 'є практичним доповненням до замовлення й допомагає налаштувати інтенсивність смаку під власні вподобання.',
                    'en' => 'is a practical addition to the order and helps adjust flavor intensity to personal preference.',
                ],
            ],
            'napoje' => [
                'intro' => [
                    'pl' => 'Napoje dopełniają zamówienie i dobrze równoważą intensywność sosów, tempury, ramenu albo pikantnych dodatków.',
                    'uk' => 'Напої доповнюють замовлення й добре балансують інтенсивність соусів, темпури, рамену або пікантних додатків.',
                    'en' => 'Drinks complete the order and help balance the intensity of sauces, tempura, ramen or spicy toppings.',
                ],
                'seo' => [
                    'pl' => 'To prosta część menu, ale ważna przy zamówieniach online, zwłaszcza gdy wybierasz sushi albo ciepłe dania na wynos.',
                    'uk' => 'Це проста, але важлива частина меню для онлайн-замовлень, особливо коли обираєте суші або гарячі страви з собою.',
                    'en' => 'This is a simple but important part of the menu for online orders, especially with sushi or warm takeaway dishes.',
                ],
                'item' => [
                    'pl' => 'uzupełnia posiłek i pomaga zbalansować intensywne, słone albo pikantne smaki.',
                    'uk' => 'доповнює їжу й допомагає збалансувати насичені, солоні або пікантні смаки.',
                    'en' => 'completes the meal and helps balance intense, salty or spicy flavors.',
                ],
            ],
            'menu-sezonowe' => [
                'intro' => [
                    'pl' => 'Menu sezonowe pokazuje pozycje dostępne czasowo, często związane ze świeżymi składnikami i krótszą ofertą.',
                    'uk' => 'Сезонне меню показує позиції, доступні тимчасово, часто пов’язані зі свіжими інгредієнтами та коротшою пропозицією.',
                    'en' => 'The seasonal menu presents items available for a limited time, often connected with fresh ingredients and a shorter offer.',
                ],
                'seo' => [
                    'pl' => 'Warto zaglądać tu regularnie, bo sezonowe sushi i dania azjatyckie w Toruniu mogą zmieniać się szybciej niż stałe menu.',
                    'uk' => 'Варто заглядати сюди регулярно, бо сезонні суші й азійські страви в Торуні можуть змінюватися швидше, ніж основне меню.',
                    'en' => 'It is worth checking this section regularly because seasonal sushi and Asian dishes in Toruń may change faster than the regular menu.',
                ],
                'item' => [
                    'pl' => 'ma sezonowy charakter i pozwala spróbować czegoś mniej oczywistego niż stałe pozycje menu.',
                    'uk' => 'має сезонний характер і дає змогу спробувати щось менш очевидне, ніж постійні позиції меню.',
                    'en' => 'has a seasonal character and lets you try something less obvious than the regular menu items.',
                ],
            ],
            'dania-dla-dzieci' => [
                'intro' => [
                    'pl' => 'Dania dla dzieci są prostsze, łagodniejsze i wygodne wtedy, gdy zamawiasz jedzenie dla całej rodziny.',
                    'uk' => 'Дитячі страви простіші, м’якші на смак і зручні, коли замовляєте їжу для всієї родини.',
                    'en' => 'Kids dishes are simpler, milder and convenient when ordering food for the whole family.',
                ],
                'seo' => [
                    'pl' => 'Ta kategoria pomaga wybrać coś dla najmłodszych gości restauracji sushi w Toruniu, bez intensywnych sosów i trudnych smaków.',
                    'uk' => 'Ця категорія допомагає обрати щось для наймолодших гостей суші-ресторану в Торуні, без інтенсивних соусів і складних смаків.',
                    'en' => 'This category helps choose something for the youngest guests of a sushi restaurant in Toruń, without intense sauces or difficult flavors.',
                ],
                'item' => [
                    'pl' => 'jest łagodniejszą propozycją, dobrą dla dzieci i rodzinnych zamówień.',
                    'uk' => 'є м’якшою пропозицією, доброю для дітей і сімейних замовлень.',
                    'en' => 'is a milder option, suitable for kids and family orders.',
                ],
            ],
        ];

        return $profiles[$slug] ?? [
            'intro' => [
                'pl' => 'Ta część menu zbiera dania przygotowywane na miejscu z myślą o świeżym, azjatyckim smaku.',
                'uk' => 'Ця частина меню об’єднує страви, які готуються на місці з акцентом на свіжий азійський смак.',
                'en' => 'This part of the menu brings together dishes prepared on site with a fresh Asian flavor in mind.',
            ],
            'seo' => [
                'pl' => 'To praktyczna kategoria dla osób szukających sushi i kuchni azjatyckiej w Toruniu z jasnym składem i cenami.',
                'uk' => 'Це практична категорія для тих, хто шукає суші та азійську кухню в Торуні з прозорим складом і цінами.',
                'en' => 'This is a practical category for guests looking for sushi and Asian food in Toruń with clear ingredients and prices.',
            ],
            'item' => [
                'pl' => 'łączy świeże przygotowanie z czytelnym smakiem charakterystycznym dla kuchni azjatyckiej.',
                'uk' => 'поєднує свіже приготування зі зрозумілим смаком, характерним для азійської кухні.',
                'en' => 'combines fresh preparation with a clear flavor typical of Asian cuisine.',
            ],
        ];
    }
}
