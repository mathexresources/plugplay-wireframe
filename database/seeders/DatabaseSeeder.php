<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarUpgrade;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Guide;
use App\Models\Order;
use App\Models\Page;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\SavedPlan;
use App\Models\ShippingMethod;
use App\Models\Tool;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------- cars ----------
        $models = [];
        foreach ([
            ['BMW', 'Řada 3', 'E46', 1998, 2006, 'Sedan,Touring,Coupé,Cabrio,Compact', true,
                'BMW řady 3 (E46) vyrábělo BMW v letech 1998–2006 jako sedan, Touring, Coupé, Cabrio a Compact. Originální rádia Business CD a Navigation neumí Bluetooth ani USB, ale díky konektoru CD měniče v kufru se dají snadno rozšířit. Všechny produkty v této sekci jsme vyzkoušeli v našem E46 Touring z roku 2003.'],
            ['Škoda', 'Octavia I', null, 1996, 2010, 'Liftback,Combi', true,
                'Škoda Octavia první generace patří k nejrozšířenějším autům u nás. Má jednoduchou elektroinstalaci, rádio s konektorem ISO a spoustu místa pro USB nabíjení ve středové konzoli. Produkty jsme ověřili v Octavii Combi 1.9 TDI z roku 2004.'],
            ['VW', 'Golf IV', null, 1997, 2006, 'Hatchback,Variant,Cabrio', true,
                'VW Golf IV sdílí elektroniku s Bora, Octavií I a Fabií I, takže spousta upgradů pasuje napříč koncernem. Rádia RCD 200/300 mají konektor Quadlock, do kterého zacvakneš Bluetooth adaptér bez stříhání.'],
            ['Škoda', 'Fabia I', null, 1999, 2007, 'Hatchback,Combi,Sedan', true,
                'Fabia první generace je oblíbené první auto a rodinný druhý vůz. Upgrady pro ni jsou levné a montáž většinou zvládneš za odpoledne s jedním šroubovákem.'],
            ['Audi', 'A4', 'B6', 2000, 2004, 'Sedan,Avant,Cabrio', true,
                'Audi A4 B6 má kvalitní interiér, ale zastaralou multimediální výbavu. Rádia Concert a Symphony se dají rozšířit o Bluetooth přes konektor CD měniče.'],
            ['Ford', 'Focus', 'Mk1', 1998, 2004, 'Hatchback,Combi,Sedan', false,
                'Ford Focus první generace – oblíbený pro jízdní vlastnosti. Pro něj máme hlavně univerzální upgrady a osvětlení.'],
            ['Opel', 'Astra', 'G', 1998, 2004, 'Hatchback,Caravan,Sedan', false,
                'Opel Astra G – jednoduché auto s ISO konektorem rádia, ideální na první upgrady.'],
        ] as $i => [$brand, $name, $code, $from, $to, $bodies, $popular, $desc]) {
            $models[$code ?: $name] = CarModel::create([
                'brand' => $brand, 'name' => $name, 'code' => $code,
                'slug' => Str::slug($brand.' '.($code ?: $name)), 'year_from' => $from, 'year_to' => $to,
                'body_types' => $bodies, 'is_popular' => $popular, 'sort' => $i, 'description' => $desc,
            ]);
        }
        $e46 = $models['E46']; $oct = $models['Octavia I']; $golf = $models['Golf IV'];
        $fab = $models['Fabia I']; $a4 = $models['B6'];

        // ---------- zones (hotspot positions on the blueprint) ----------
        $zones = [];
        foreach ([
            ['Interiér a nabíjení', 'interier', 'bi-plug', 42, 32],
            ['Rádio a Bluetooth', 'radio', 'bi-music-note-beamed', 57, 55],
            ['Světla', 'svetla', 'bi-lightbulb', 92, 68],
            ['Zamykání a okna', 'zamykani', 'bi-key', 30, 72],
            ['Diagnostika a kódování', 'diagnostika', 'bi-cpu', 77, 40],
            ['Baterie (v kufru)', 'baterie', 'bi-battery-charging', 16, 22],
            ['Parkování', 'parkovani', 'bi-p-square', 9, 66],
        ] as $i => [$n, $s, $icon, $x, $y]) {
            $zones[$s] = Zone::create(['name' => $n, 'slug' => $s, 'icon' => $icon, 'pos_x' => $x, 'pos_y' => $y, 'sort' => $i]);
        }

        // ---------- categories ----------
        $cats = [];
        foreach ([
            ['Nabíjení a USB', 'nabijeni-a-usb', 'bi-lightning-charge', 'Rozhoduje hlavně výkon a typ konektoru. Pro rychlé nabíjení moderního telefonu stačí USB-C s výkonem 30 W, notebook nebo tablet potřebují 60 W a víc. Nabíječky do autozásuvky 12 V jen zastrčíš, ale zabírají místo po zapalovači. Čistší řešení je USB-C modul napevno místo zapalovače nebo do záslepky v palubní desce.'],
            ['Rádio, Bluetooth a zvuk', 'radio-bluetooth-a-zvuk', 'bi-music-note-beamed', 'Bluetooth adaptéry do originálních rádií, handsfree a reproduktory. Originální rádio zůstane, jen umí víc.'],
            ['Osvětlení', 'osvetleni', 'bi-lightbulb', 'LED osvětlení interiéru, SPZ i denní svícení – vždy s ohledem na kontrolu žárovek, aby auto nehlásilo chybu.'],
            ['Zamykání a zabezpečení', 'zamykani-a-zabezpeceni', 'bi-shield-lock', 'Moduly komfortního zamykání, automatické stahování oken a jednoduché alarmy.'],
            ['Parkovací senzory a kamery', 'parkovaci-senzory-a-kamery', 'bi-p-square', 'Zadní i přední parkovací senzory a couvací kamery do starších aut.'],
            ['Baterie a monitoring', 'baterie-a-monitoring', 'bi-battery-charging', 'Hlídání napětí baterie, odpojovače a nabíječky.'],
            ['Diagnostika a kódování', 'diagnostika-a-kodovani', 'bi-cpu', 'OBD diagnostika a kabely pro kódování skrytých funkcí.'],
            ['Kabeláže a konektory', 'kabelaze-a-konektory', 'bi-diagram-3', 'Redukce, kabeláže a konektory pro čisté zapojení bez stříhání.'],
            ['Nářadí', 'naradi', 'bi-tools', 'Nářadí, které se hodí při montáži autoelektroniky.'],
        ] as $i => [$n, $s, $icon, $d]) {
            $cats[$s] = Category::create(['name' => $n, 'slug' => $s, 'icon' => $icon, 'description' => $d, 'sort' => $i]);
        }
        foreach ([['USB-C nabíječky do auta', 'usb-c-nabijecky'], ['Bezdrátové nabíječky', 'bezdratove-nabijecky'], ['Autozásuvky a rozbočovače 12 V', 'autozasuvky-a-rozbocovace']] as $i => [$n, $s]) {
            $cats[$s] = Category::create(['parent_id' => $cats['nabijeni-a-usb']->id, 'name' => $n, 'slug' => $s, 'icon' => 'bi-usb-c', 'sort' => $i]);
        }

        // ---------- tools ----------
        $p = fn (array $a) => Product::create($a + ['is_active' => true]);
        $packy = $p(['category_id' => $cats['naradi']->id, 'name' => 'Sada plastových páček na interiér', 'slug' => 'sada-plastovych-packy', 'sku' => 'PP-PACKY', 'price' => 99, 'stock' => 120, 'difficulty' => 1, 'install_minutes' => 0, 'install_priority' => 99, 'is_universal' => true, 'manufacturer' => 'PlugPlay', 'short_description' => '12 dílů, nepoškrábou plasty.', 'description' => 'Sada 12 plastových páček pro bezpečné sundání krytů interiéru bez poškrábání.', 'specs' => "Počet dílů: 12\nMateriál: nylon"]);
        $klic = $p(['category_id' => $cats['naradi']->id, 'name' => 'Klíč 10 mm na svorku baterie', 'slug' => 'klic-10-mm', 'sku' => 'PP-KLIC10', 'price' => 79, 'stock' => 60, 'difficulty' => 1, 'install_minutes' => 0, 'install_priority' => 99, 'is_universal' => true, 'manufacturer' => 'Yato', 'short_description' => 'Očkoplochý klíč 10 mm.', 'description' => 'Očkoplochý klíč 10 mm – přesně na svorky baterie většiny aut.']);
        $torxSet = $p(['category_id' => $cats['naradi']->id, 'name' => 'Sada bitů Torx T10–T40', 'slug' => 'sada-bitu-torx', 'sku' => 'PP-TORX', 'price' => 149, 'stock' => 40, 'difficulty' => 1, 'install_minutes' => 0, 'install_priority' => 99, 'is_universal' => true, 'manufacturer' => 'Yato', 'short_description' => '7 bitů s držákem.', 'description' => 'Sada bitů Torx T10, T15, T20, T25, T27, T30, T40 s magnetickým držákem.']);
        $tools = [
            'sroubovak' => Tool::create(['name' => 'Plochý šroubovák']),
            'packy' => Tool::create(['name' => 'Plastové páčky na interiér', 'product_id' => $packy->id]),
            'torx' => Tool::create(['name' => 'Torx T20', 'product_id' => $torxSet->id]),
            'klic' => Tool::create(['name' => 'Klíč 10 mm na svorku baterie', 'product_id' => $klic->id]),
            'vrtacka' => Tool::create(['name' => 'Vrtačka a korunkový vrták 22 mm']),
        ];

        // ---------- products ----------
        $prod = function (array $a, array $fits = [], array $toolKeys = []) use ($p, $tools) {
            $product = $p($a);
            $product->carModels()->sync(collect($fits)->pluck('id'));
            $product->tools()->sync(collect($toolKeys)->map(fn ($k) => $tools[$k]->id));
            return $product;
        };
        $common = [$e46, $oct, $golf, $fab, $a4];

        $usbc = $prod(['category_id' => $cats['usb-c-nabijecky']->id, 'zone_id' => $zones['interier']->id, 'name' => 'USB-C nabíječka 60 W místo zapalovače', 'slug' => 'usb-c-nabijecka-60w-misto-zapalovace', 'sku' => 'PP-USBC60', 'price' => 590, 'stock' => 48, 'difficulty' => 1, 'install_minutes' => 15, 'install_priority' => 20, 'goal' => 'nabijeni', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'location_in_car' => 'středová konzole', 'short_description' => 'USB-C PD 60 W do otvoru po zapalovači, vypadá jako originál.', 'description' => 'Nabíječka se zasadí přesně do otvoru po zapalovači a připojí se originálním konektorem – žádné stříhání kabelů. Nabije telefon, tablet i notebook přes USB-C Power Delivery.', 'test_score' => 9, 'test_quote' => 'Rychlé nabíjení jako v novém autě. A za čtvrt hodiny je hotovo.', 'pros' => "Nabije i notebook (USB-C PD 60 W)\nVypadá jako originální díl\nŽádné stříhání kabelů", 'cons' => "Přijdeš o zapalovač\nPři plném výkonu mírně hřeje", 'specs' => "Výkon: 60 W (USB-C Power Delivery)\nNapájení: 12 V z palubní sítě\nMontáž: do otvoru po zapalovači\nObsah balení: nabíječka, rámeček, kabeláž s konektorem, tištěný návod\nZáruka: 24 měsíců", 'variant_label' => 'Barva rámečku', 'variant_options' => 'Černá matná,Chrom', 'sold_count' => 412], $common, ['sroubovak', 'packy']);
        $bt = $prod(['category_id' => $cats['radio-bluetooth-a-zvuk']->id, 'zone_id' => $zones['radio']->id, 'name' => 'Bluetooth adaptér do konektoru CD měniče', 'slug' => 'bluetooth-adapter-do-konektoru-cd-menice', 'sku' => 'PP-BTCD', 'price' => 1490, 'stock' => 22, 'difficulty' => 2, 'install_minutes' => 30, 'install_priority' => 10, 'goal' => 'hudba', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'location_in_car' => 'kufr, levá strana', 'install_tip' => 'Začni tímhle: před montáží odpoj baterii. V E46 je taky v kufru, takže ji máš po ruce.', 'short_description' => 'Hudba a handsfree v originálním rádiu Business CD / Navigation.', 'description' => 'Adaptér se zapojí do konektoru CD měniče v kufru. Rádio ho pozná jako měnič, takže ovládání z volantu funguje dál. Součástí je mikrofon pro handsfree.', 'test_score' => 8, 'test_quote' => 'Zvuk čistší než přes FM transmitter a ovládání z volantu funguje.', 'pros' => "Funguje ovládání na volantu\nHandsfree s mikrofonem\nOriginální rádio zůstane", 'cons' => "Mikrofon je potřeba vést k sloupku\nPřehrávání se po vypnutí auta zastaví", 'specs' => "Bluetooth: 5.3, A2DP, HFP\nKodeky: SBC, AAC\nKonektor: 3pin/6pin CD měnič (dle auta)\nZáruka: 24 měsíců", 'sold_count' => 287], [$e46, $a4], ['torx', 'klic']);
        $bt2 = $prod(['category_id' => $cats['radio-bluetooth-a-zvuk']->id, 'zone_id' => $zones['radio']->id, 'name' => 'Bluetooth adaptér Quadlock pro rádia RCD/Symphony', 'slug' => 'bluetooth-adapter-quadlock', 'sku' => 'PP-BTQL', 'price' => 1290, 'stock' => 15, 'difficulty' => 2, 'install_minutes' => 40, 'install_priority' => 10, 'goal' => 'hudba', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'location_in_car' => 'za rádiem', 'install_tip' => 'Na vytažení rádia budeš potřebovat vytahovací klíče – jsou v balení.', 'short_description' => 'Pro koncernová rádia s konektorem Quadlock.', 'description' => 'Zacvakne se mezi rádio a konektor Quadlock. Hudba z telefonu i handsfree bez stříhání.', 'test_score' => 8, 'test_quote' => 'Po 40 minutách hraje Spotify z originálního rádia.', 'pros' => "Bez stříhání kabelů\nVytahovací klíče v balení", 'cons' => "Rádio je potřeba vytáhnout"], [$oct, $golf, $fab], ['sroubovak']);
        $wireless = $prod(['category_id' => $cats['bezdratove-nabijecky']->id, 'zone_id' => $zones['interier']->id, 'name' => 'Bezdrátová nabíječka Qi 15 W do odkládací schránky', 'slug' => 'bezdratova-nabijecka-qi-15w', 'sku' => 'PP-QI15', 'price' => 890, 'stock' => 0, 'restock_days' => 5, 'difficulty' => 2, 'install_minutes' => 30, 'install_priority' => 30, 'goal' => 'nabijeni', 'manufacturer' => 'Baseus', 'location_in_car' => 'odkládací schránka před řadicí pákou', 'short_description' => 'Podložka Qi s protiskluzovou úpravou na míru schránce.', 'description' => 'Bezdrátová nabíječka Qi 15 W, napájení vedeš ze zásuvky 12 V pod konzolí.', 'test_score' => 7, 'test_quote' => 'Pohodlné, ale iPhone v pouzdře nabíjí pomaleji.', 'pros' => "Telefon jen položíš\nProtiskluzová podložka", 'cons' => "Pomalejší než kabel\nHřeje při navigaci"], [$e46, $oct, $golf], ['packy']);
        $volt = $prod(['category_id' => $cats['autozasuvky-a-rozbocovace']->id, 'zone_id' => $zones['interier']->id, 'name' => 'Voltmetr s USB do autozásuvky 12 V', 'slug' => 'voltmetr-s-usb-12v', 'sku' => 'PP-VOLT', 'price' => 249, 'stock' => 80, 'difficulty' => 1, 'install_minutes' => 1, 'install_priority' => 40, 'goal' => 'nabijeni', 'manufacturer' => 'Ugreen', 'is_universal' => true, 'location_in_car' => 'autozásuvka', 'short_description' => 'Ukáže napětí baterie a nabije telefon.', 'description' => 'Zastrčíš do zásuvky 12 V a hned vidíš, v jaké kondici je baterie. Dva porty USB-A 18 W.', 'test_score' => 8, 'pros' => "Jen zastrčíš\nVidíš stav baterie", 'cons' => "Zabírá zásuvku"]);
        $dual = $prod(['category_id' => $cats['usb-c-nabijecky']->id, 'zone_id' => $zones['interier']->id, 'name' => 'Dvojitá zásuvka USB-C a USB-A 60 W do palubní desky', 'slug' => 'dvojita-zasuvka-usb-c-usb-a-60w', 'sku' => 'PP-DUAL60', 'price' => 425, 'old_price' => 499, 'stock' => 30, 'difficulty' => 2, 'install_minutes' => 40, 'install_priority' => 25, 'goal' => 'nabijeni', 'manufacturer' => 'Baseus', 'is_universal' => true, 'location_in_car' => 'záslepka v palubní desce', 'short_description' => 'Zapustí se do záslepky, vrták 22 mm.', 'description' => 'Univerzální zásuvka do otvoru 22 mm. Napájení odbočíš ze zásuvky 12 V.', 'pros' => "Zůstane ti zapalovač\nDva telefony najednou", 'cons' => "Je potřeba vrtat"], [], ['vrtacka', 'packy']);
        $dualplug = $prod(['category_id' => $cats['usb-c-nabijecky']->id, 'zone_id' => $zones['interier']->id, 'name' => 'Nabíječka do autozásuvky 12 V, 2× USB-C', 'slug' => 'nabijecka-do-autozasuvky-2x-usb-c', 'sku' => 'PP-PLUG2C', 'price' => 349, 'stock' => 140, 'difficulty' => 1, 'install_minutes' => 1, 'install_priority' => 40, 'goal' => 'nabijeni', 'manufacturer' => 'Ugreen', 'is_universal' => true, 'short_description' => 'Malá, kovová, 2× 30 W.', 'description' => 'Nejjednodušší cesta k rychlému nabíjení – jen ji zastrčíš.', 'sold_count' => 530]);
        $splitter = $prod(['category_id' => $cats['autozasuvky-a-rozbocovace']->id, 'zone_id' => $zones['interier']->id, 'name' => 'Rozbočovač autozásuvky 12 V: 3 zásuvky a 2× USB-C', 'slug' => 'rozbocovac-autozasuvky-3x-2x-usb-c', 'sku' => 'PP-SPLIT3', 'price' => 459, 'stock' => 25, 'difficulty' => 1, 'install_minutes' => 1, 'install_priority' => 40, 'goal' => 'nabijeni', 'manufacturer' => 'Baseus', 'is_universal' => true, 'short_description' => 'Na cesty s celou rodinou.', 'description' => 'Tři zásuvky 12 V s vypínači a dva porty USB-C.']);
        $usbmod = $prod(['category_id' => $cats['usb-c-nabijecky']->id, 'zone_id' => $zones['interier']->id, 'name' => 'Univerzální USB-C modul do záslepky palubní desky', 'slug' => 'univerzalni-usb-c-modul-do-zaslepky', 'sku' => 'PP-USBMOD', 'price' => 390, 'stock' => 18, 'difficulty' => 2, 'install_minutes' => 30, 'install_priority' => 25, 'goal' => 'nabijeni', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'is_universal' => true, 'short_description' => 'Záslepka s USB-C 30 W.', 'description' => 'Náhrada záslepky pro koncernová auta i univerzální verze.'], [], ['packy']);
        $ledspz = $prod(['category_id' => $cats['osvetleni']->id, 'zone_id' => $zones['svetla']->id, 'name' => 'LED osvětlení SPZ bez chybového hlášení', 'slug' => 'led-osvetleni-spz-bez-chyby', 'sku' => 'PP-LEDSPZ', 'price' => 390, 'stock' => 55, 'difficulty' => 1, 'install_minutes' => 15, 'install_priority' => 60, 'goal' => 'bezpecnost', 'manufacturer' => 'PlugPlay', 'location_in_car' => 'zadní nárazník', 'short_description' => 'S rezistorem, auto nehlásí vadnou žárovku.', 'description' => 'Kompletní lampičky SPZ s LED a zabudovaným odporem – kontrola žárovek nic nepozná.', 'test_score' => 9, 'pros' => "Bez chybového hlášení\nBílé světlo 6000 K", 'cons' => "Nesmí se kombinovat s halogenem"], [$e46, $golf, $oct, $fab], ['sroubovak']);
        $ledfoot = $prod(['category_id' => $cats['osvetleni']->id, 'zone_id' => $zones['interier']->id, 'name' => 'LED osvětlení prostoru pro nohy', 'slug' => 'led-osvetleni-prostoru-pro-nohy', 'sku' => 'PP-LEDFOOT', 'price' => 1290, 'stock' => 9, 'difficulty' => 3, 'install_minutes' => 120, 'install_priority' => 70, 'goal' => 'pohodli', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'location_in_car' => 'pod palubní deskou', 'short_description' => 'Rozsvítí se s otevřením dveří.', 'description' => 'Napojení na stropní osvětlení, měkký náběh a doběh.', 'cons' => "Kabeláž vedeš pod prahovými lištami"], [$e46, $a4], ['packy', 'torx']);
        $ledint = $prod(['category_id' => $cats['osvetleni']->id, 'zone_id' => $zones['svetla']->id, 'name' => 'LED sada interiérového osvětlení', 'slug' => 'led-sada-interieroveho-osvetleni', 'sku' => 'PP-LEDINT', 'price' => 490, 'stock' => 33, 'difficulty' => 1, 'install_minutes' => 20, 'install_priority' => 60, 'goal' => 'pohodli', 'manufacturer' => 'Osram', 'short_description' => 'Stropní, čtecí a kufr.', 'description' => 'Kompletní sada LED žárovek pro interiér přesně podle modelu.'], [$e46, $oct, $golf, $fab, $a4], ['packy']);
        $comfort = $prod(['category_id' => $cats['zamykani-a-zabezpeceni']->id, 'zone_id' => $zones['zamykani']->id, 'name' => 'Modul komfortního zavírání oken', 'slug' => 'modul-komfortniho-zavirani-oken', 'sku' => 'PP-COMFORT', 'price' => 990, 'stock' => 12, 'difficulty' => 3, 'install_minutes' => 90, 'install_priority' => 50, 'goal' => 'pohodli', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'location_in_car' => 'za levým prahem', 'short_description' => 'Zamkneš klíčem a okna se sama zavřou.', 'description' => 'Modul se připojí ke konektoru řídicí jednotky komfortu.'], [$golf, $oct, $fab], ['packy', 'torx']);
        $autolock = $prod(['category_id' => $cats['zamykani-a-zabezpeceni']->id, 'zone_id' => $zones['zamykani']->id, 'name' => 'Automatické zamknutí při rozjezdu', 'slug' => 'automaticke-zamknuti-pri-rozjezdu', 'sku' => 'PP-AUTOLOCK', 'price' => 690, 'stock' => 20, 'difficulty' => 2, 'install_minutes' => 30, 'install_priority' => 50, 'goal' => 'bezpecnost', 'manufacturer' => 'PlugPlay', 'own_production' => true, 'short_description' => 'Zamkne nad 15 km/h, odemkne po vypnutí.', 'description' => 'Bezpečnost ve městě – na křižovatce ti nikdo neotevře dveře.'], [$e46, $golf, $oct]);
        $pdc = $prod(['category_id' => $cats['parkovaci-senzory-a-kamery']->id, 'zone_id' => $zones['parkovani']->id, 'name' => 'Zadní parkovací senzory se zvukovou signalizací', 'slug' => 'zadni-parkovaci-senzory', 'sku' => 'PP-PDC4', 'price' => 1190, 'stock' => 14, 'difficulty' => 3, 'install_minutes' => 180, 'install_priority' => 80, 'goal' => 'bezpecnost', 'manufacturer' => 'Steelmate', 'is_universal' => true, 'location_in_car' => 'zadní nárazník', 'short_description' => '4 senzory, lakovatelné.', 'description' => 'Čtyři senzory do nárazníku, řídicí jednotka v kufru a bzučák.', 'cons' => "Je potřeba vrtat nárazník"], [], ['vrtacka', 'klic']);
        $cam = $prod(['category_id' => $cats['parkovaci-senzory-a-kamery']->id, 'zone_id' => $zones['parkovani']->id, 'name' => 'Couvací kamera do rámečku SPZ', 'slug' => 'couvaci-kamera-do-ramecku-spz', 'sku' => 'PP-CAM', 'price' => 1590, 'stock' => 7, 'difficulty' => 3, 'install_minutes' => 150, 'install_priority' => 80, 'goal' => 'bezpecnost', 'manufacturer' => 'Steelmate', 'is_universal' => true, 'short_description' => 'S displejem do zrcátka.', 'description' => 'Kamera v rámečku SPZ a displej, který se nasadí na vnitřní zrcátko.'], [], ['klic', 'packy']);
        $batmon = $prod(['category_id' => $cats['baterie-a-monitoring']->id, 'zone_id' => $zones['baterie']->id, 'name' => 'Bluetooth monitor baterie', 'slug' => 'bluetooth-monitor-baterie', 'sku' => 'PP-BATMON', 'price' => 449, 'stock' => 60, 'difficulty' => 1, 'install_minutes' => 10, 'install_priority' => 5, 'goal' => 'bezpecnost', 'manufacturer' => 'Ancel', 'is_universal' => true, 'location_in_car' => 'na svorkách baterie', 'short_description' => 'Napětí a historie v aplikaci.', 'description' => 'Připojíš na svorky baterie a v telefonu vidíš, jestli baterie vydrží zimu.', 'test_score' => 8], [], ['klic']);
        $odpoj = $prod(['category_id' => $cats['baterie-a-monitoring']->id, 'zone_id' => $zones['baterie']->id, 'name' => 'Odpojovač baterie s ovladačem', 'slug' => 'odpojovac-baterie-s-ovladacem', 'sku' => 'PP-ODPOJ', 'price' => 790, 'stock' => 11, 'difficulty' => 2, 'install_minutes' => 20, 'install_priority' => 5, 'goal' => 'bezpecnost', 'manufacturer' => 'Hella', 'is_universal' => true, 'short_description' => 'Na zimní odstávku veterána.', 'description' => 'Dálkově odpojí baterii – auto nevybije ani nepojede.'], [], ['klic']);
        $obd = $prod(['category_id' => $cats['diagnostika-a-kodovani']->id, 'zone_id' => $zones['diagnostika']->id, 'name' => 'OBD2 Bluetooth diagnostika', 'slug' => 'obd2-bluetooth-diagnostika', 'sku' => 'PP-OBD', 'price' => 690, 'stock' => 35, 'difficulty' => 1, 'install_minutes' => 1, 'install_priority' => 90, 'goal' => 'bezpecnost', 'manufacturer' => 'Vgate', 'is_universal' => true, 'location_in_car' => 'pod volantem', 'short_description' => 'Chyby motoru v telefonu.', 'description' => 'Přečte a smaže chyby, ukáže živá data motoru.', 'test_score' => 9, 'sold_count' => 198]);
        $kdcan = $prod(['category_id' => $cats['diagnostika-a-kodovani']->id, 'zone_id' => $zones['diagnostika']->id, 'name' => 'Kabel K+DCAN pro kódování BMW', 'slug' => 'kabel-k-dcan-pro-kodovani-bmw', 'sku' => 'PP-KDCAN', 'price' => 590, 'stock' => 10, 'difficulty' => 3, 'install_minutes' => 60, 'install_priority' => 90, 'goal' => 'pohodli', 'manufacturer' => 'PlugPlay', 'short_description' => 'Pro INPA / NCS Expert.', 'description' => 'Odemkni skryté funkce: komfortní blinkr, zavírání oken dálkovým ovladačem…'], [$e46]);
        $vag = $prod(['category_id' => $cats['diagnostika-a-kodovani']->id, 'zone_id' => $zones['diagnostika']->id, 'name' => 'Kabel VAG-COM KKL pro koncernová auta', 'slug' => 'kabel-vag-com-kkl', 'sku' => 'PP-KKL', 'price' => 390, 'stock' => 16, 'difficulty' => 3, 'install_minutes' => 60, 'install_priority' => 90, 'goal' => 'pohodli', 'manufacturer' => 'PlugPlay', 'short_description' => 'Kódování Škoda/VW/Audi.', 'description' => 'Diagnostika a kódování koncernových aut do roku 2005.'], [$oct, $golf, $fab, $a4]);
        $iso = $prod(['category_id' => $cats['kabelaze-a-konektory']->id, 'zone_id' => $zones['radio']->id, 'name' => 'Redukce ISO pro výměnu rádia', 'slug' => 'redukce-iso', 'sku' => 'PP-ISO', 'price' => 149, 'stock' => 90, 'difficulty' => 1, 'install_minutes' => 10, 'install_priority' => 15, 'goal' => 'hudba', 'manufacturer' => 'Connects2', 'short_description' => 'Napájení a reproduktory.', 'description' => 'Redukce pro připojení nerádia s ISO konektorem.'], [$oct, $golf, $fab, $e46]);
        $antena = $prod(['category_id' => $cats['kabelaze-a-konektory']->id, 'zone_id' => $zones['radio']->id, 'name' => 'Anténní redukce DIN – Fakra', 'slug' => 'antenni-redukce-din-fakra', 'sku' => 'PP-ANT', 'price' => 99, 'stock' => 70, 'difficulty' => 1, 'install_minutes' => 5, 'install_priority' => 15, 'goal' => 'hudba', 'manufacturer' => 'Connects2', 'is_universal' => true, 'short_description' => 'Pro výměnu rádia.', 'description' => 'Anténní redukce z DIN na Fakru a zpět.']);
        $speakers = $prod(['category_id' => $cats['radio-bluetooth-a-zvuk']->id, 'zone_id' => $zones['radio']->id, 'name' => 'Reproduktory do předních dveří 165 mm', 'slug' => 'reproduktory-165mm', 'sku' => 'PP-SPK165', 'price' => 1190, 'stock' => 8, 'difficulty' => 2, 'install_minutes' => 60, 'install_priority' => 45, 'goal' => 'hudba', 'manufacturer' => 'JBL', 'short_description' => 'Koaxiální, s redukcemi.', 'description' => 'Plug & play reproduktory včetně distančních kroužků a konektorů.'], [$golf, $oct, $fab], ['torx', 'packy']);

        // ---------- bundles ----------
        $b = Bundle::create(['car_model_id' => $e46->id, 'name' => 'Modernizace E46 za jedno odpoledne', 'slug' => 'modernizace-e46', 'price' => 2190, 'description' => 'Hotová sada pro nerozhodné: nabíjení, hudba a LED SPZ.']);
        $b->products()->sync([$usbc->id, $bt->id, $ledspz->id]);
        $b = Bundle::create(['car_model_id' => $oct->id, 'name' => 'Octavia I: Bluetooth a USB-C', 'slug' => 'octavia-bluetooth-usb-c', 'price' => 1690, 'description' => 'Dvě nejčastější přání majitelů Octavie I.']);
        $b->products()->sync([$usbc->id, $bt2->id]);
        $b = Bundle::create(['car_model_id' => $golf->id, 'name' => 'Golf IV: pohodlí na každý den', 'slug' => 'golf-pohodli', 'price' => 2290, 'description' => 'Komfortní zavírání oken, USB-C a LED interiér.']);
        $b->products()->sync([$comfort->id, $usbc->id, $ledint->id]);

        // ---------- guides ----------
        $guide = fn ($product, $model, $title, $steps, $video = null) => Guide::create([
            'product_id' => $product->id, 'car_model_id' => $model?->id, 'title' => $title, 'slug' => Str::slug($title),
            'intro' => 'Návod krok za krokem s fotkami. Přečti si ho celý ještě před montáží.', 'video_minutes' => $video,
            'steps' => collect($steps)->map(fn ($s) => ['title' => $s[0], 'body' => $s[1]])->all(),
        ]);
        $guide($usbc, $e46, 'USB-C nabíječka v BMW E46', [
            ['Vyndej popelník a jeho vložku', 'Otevři popelník a vytáhni plastovou vložku směrem nahoru.'],
            ['Vysuň objímku a odpoj konektor', 'Plastovou páčkou vysuň objímku zapalovače a odpoj dvoupinový konektor.'],
            ['Připoj kabeláž nabíječky', 'Konektor z balení zacvakni do originálního konektoru – nic se nestříhá.'],
            ['Zasaď nabíječku', 'Nabíječku zasuň do otvoru, dokud necvakne.'],
            ['Vrať popelník', 'Vlož zpět vložku popelníku.'],
            ['Vyzkoušej', 'Zapni zapalování a připoj telefon. Kontrolka svítí modře.'],
        ], 4);
        $guide($usbc, null, 'USB-C nabíječka místo zapalovače – obecný návod', [
            ['Odpoj napájení', 'Vypni zapalování a vytáhni klíček.'],
            ['Vyndej zapalovač', 'Vytáhni zapalovač a páčkou objímku.'],
            ['Zapoj nabíječku', 'Použij redukci z balení podle konektoru tvého auta.'],
            ['Zasaď a vyzkoušej', 'Nabíječku zacvakni a vyzkoušej nabíjení.'],
        ]);
        $guide($bt, $e46, 'Bluetooth adaptér v BMW E46', [
            ['Odpoj baterii', 'Baterie je v kufru vpravo. Klíčem 10 mm povol mínusovou svorku.'],
            ['Sundej levý boční kryt kufru', 'Torx T20 a dvě plastové matky.'],
            ['Najdi konektor CD měniče', 'Na levé straně kufru je černý konektor – u aut bez měniče bývá zaslepený.'],
            ['Zapoj adaptér', 'Konektor adaptéru zacvakni a modul přilep suchým zipem.'],
            ['Veď mikrofon', 'Mikrofon veď pod čalouněním k A sloupku.'],
            ['Spáruj telefon', 'Na rádiu zvol CD měnič a spáruj telefon s „PlugPlay BT“.'],
            ['Připoj baterii', 'Utáhni svorku a nastav znovu hodiny.'],
            ['Vrať kryty', 'Nasaď boční kryt kufru.'],
        ], 6);
        $guide($ledspz, $e46, 'LED osvětlení SPZ v BMW E46', [
            ['Vyšroubuj lampičku', 'Plochým šroubovákem vypáčni lampičku z nárazníku.'],
            ['Odpoj konektor', 'Stiskni pojistku a odpoj konektor.'],
            ['Zapoj LED lampičku', 'Zacvakni konektor nové lampičky.'],
            ['Zasaď a vyzkoušej', 'Lampičku zatlač zpět a rozsviť světla.'],
            ['Druhá strana', 'Stejně postupuj na druhé straně.'],
        ], 3);
        $guide($bt2, $oct, 'Bluetooth Quadlock v Octavii I', [
            ['Vytáhni rádio', 'Zasuň vytahovací klíče do otvorů v rádiu a vytáhni ho.'],
            ['Odpoj Quadlock', 'Zvedni páčku a odpoj velký konektor.'],
            ['Vlož adaptér', 'Adaptér zapoj mezi konektor a rádio.'],
            ['Vrať rádio a spáruj', 'Zasuň rádio a spáruj telefon.'],
        ], 5);
        $guide($obd, null, 'Jak připojit OBD2 diagnostiku', [
            ['Najdi OBD zásuvku', 'Bývá pod volantem nebo za krytem pojistek.'],
            ['Zapoj adaptér', 'Zapoj adaptér a zapni zapalování.'],
            ['Spáruj aplikaci', 'V aplikaci Car Scanner vyber Vgate a připoj se.'],
        ]);

        // ---------- shipping & payment ----------
        ShippingMethod::create(['name' => 'Zásilkovna – výdejní místo', 'description' => 'Doručení do 2 pracovních dnů', 'icon' => 'bi-box-seam', 'price' => 69, 'needs_pickup_point' => true, 'sort' => 1]);
        ShippingMethod::create(['name' => 'Zásilkovna – doručení na adresu', 'description' => 'Doručení do 2 pracovních dnů', 'icon' => 'bi-truck', 'price' => 99, 'needs_address' => true, 'sort' => 2]);
        ShippingMethod::create(['name' => 'Osobní odběr', 'description' => 'Po domluvě – rádi ti ukážeme, jak montáž probíhá', 'icon' => 'bi-house', 'price' => 0, 'free_over_threshold' => false, 'sort' => 3]);
        PaymentMethod::create(['code' => 'card', 'name' => 'Kartou online', 'icon' => 'bi-credit-card', 'price' => 0, 'sort' => 1]);
        PaymentMethod::create(['code' => 'transfer', 'name' => 'QR platba / převodem', 'icon' => 'bi-qr-code', 'price' => 0, 'sort' => 2]);
        PaymentMethod::create(['code' => 'cod', 'name' => 'Dobírka', 'icon' => 'bi-cash-coin', 'price' => 39, 'sort' => 3]);

        Coupon::create(['code' => 'SKOLA10', 'type' => 'percent', 'value' => 10]);
        Coupon::create(['code' => 'FOTKY10', 'type' => 'percent', 'value' => 10, 'min_total' => 500]);
        Coupon::create(['code' => 'MINUS200', 'type' => 'fixed', 'value' => 200, 'min_total' => 1500]);

        // ---------- users ----------
        $admin = User::create(['name' => 'Admin PlugPlay', 'email' => 'admin@plugplay.cz', 'password' => 'admin123']);
        $admin->forceFill(['is_admin' => true])->save();
        $petr = User::create(['name' => 'Petr Novák', 'email' => 'petr@example.cz', 'phone' => '+420 777 123 456', 'password' => 'heslo123']);
        $petr->addresses()->create(['label' => 'Domů', 'street' => 'Ulice 12', 'city' => 'Praha', 'zip' => '140 00', 'is_default' => true]);
        $car1 = Car::create(['user_id' => $petr->id, 'car_model_id' => $e46->id, 'nickname' => 'Moje E46', 'year' => 2003, 'body_type' => 'Touring', 'engine' => '2.0 diesel', 'radio' => 'Business CD', 'is_active' => true]);
        Car::create(['user_id' => $petr->id, 'car_model_id' => $fab->id, 'nickname' => 'Rodinná Fabia', 'year' => 2005]);

        $order = function (User $u, array $lines, string $status, bool $paid, string $date) use ($e46) {
            $subtotal = collect($lines)->sum(fn ($l) => $l[0]->price * $l[1]);
            $o = Order::create(['number' => '2026-'.str_pad((string) (Order::count() + 87), 4, '0', STR_PAD_LEFT), 'token' => Str::random(40),
                'user_id' => $u->id, 'car_model_id' => $e46->id, 'car_label' => $e46->short_name, 'name' => $u->name, 'email' => $u->email, 'phone' => $u->phone,
                'pickup_point' => 'Praha 4, Budějovická 1', 'shipping_name' => 'Zásilkovna – výdejní místo', 'shipping_price' => 69,
                'payment_code' => 'transfer', 'payment_name' => 'QR platba / převodem', 'payment_price' => 0,
                'subtotal' => $subtotal, 'total' => $subtotal + 69, 'status' => $status, 'is_paid' => $paid,
                'created_at' => $date, 'updated_at' => $date]);
            foreach ($lines as [$prod, $qty]) $o->items()->create(['product_id' => $prod->id, 'name' => $prod->name, 'price' => $prod->price, 'quantity' => $qty]);
            return $o;
        };
        $order($petr, [[$dual, 1]], 'delivered', true, '2026-08-03 10:12:00');
        $order($petr, [[$bt, 1], [$usbc, 1]], 'packing', true, '2026-10-22 18:40:00');
        foreach ([[$usbc, true], [$bt, true], [$ledspz, false]] as [$prod, $inst]) {
            CarUpgrade::create(['car_id' => $car1->id, 'product_id' => $prod->id, 'installed' => $inst]);
        }
        SavedPlan::create(['user_id' => $petr->id, 'car_model_id' => $fab->id, 'name' => 'Fabia – příprava na zimu', 'token' => Str::random(40),
            'items' => [['type' => 'product', 'id' => $bt2->id, 'variant' => null, 'qty' => 1], ['type' => 'product', 'id' => $batmon->id, 'variant' => null, 'qty' => 1], ['type' => 'product', 'id' => $ledint->id, 'variant' => null, 'qty' => 1]]]);

        // ---------- reviews ----------
        foreach ([
            [$usbc, 'Tomáš', 'BMW 318i (E46), 2002', $e46, 5, 10, 'Šel jsem přesně podle návodu a sedí to jako originál. Telefon nabije rychleji než doma.'],
            [$usbc, 'Jana', 'Škoda Octavia I, 2006', $oct, 5, 15, 'Konečně nabíjím notebook cestou na chatu.'],
            [$usbc, 'Martin', 'VW Golf IV, 2001', $golf, 4, 20, 'Funguje skvěle, jen při plném výkonu trochu hřeje.'],
            [$bt, 'Lukáš', 'BMW 320d (E46), 2004', $e46, 5, 35, 'Ovládání z volantu funguje, zvuk super.'],
            [$bt, 'Pavel', 'Audi A4 B6, 2003', $a4, 4, 45, 'Mikrofon jsem musel přemístit, jinak paráda.'],
            [$obd, 'Ondřej', 'Ford Focus, 2003', null, 5, 1, 'Za tu cenu nejlepší investice.'],
            [$ledspz, 'Eva', 'Škoda Fabia I, 2005', $fab, 5, 10, 'Auto nehlásí chybu, světlo krásně bílé.'],
        ] as [$prod, $name, $label, $model, $rating, $min, $body]) {
            Review::create(['product_id' => $prod->id, 'author_name' => $name, 'car_label' => $label, 'car_model_id' => $model?->id,
                'rating' => $rating, 'install_minutes' => $min, 'body' => $body, 'is_approved' => true]);
        }
        Review::create(['product_id' => $wireless->id, 'author_name' => 'Karel', 'car_label' => 'VW Golf IV', 'rating' => 3, 'body' => 'Čekám na schválení – recenze pro ukázku moderace v administraci.', 'is_approved' => false]);

        // ---------- pages ----------
        $pages = [
            ['Doprava a platba', 'doprava-a-platba', 'nakup', "<p>Zboží skladem odesíláme následující pracovní den. Vybírat můžeš ze Zásilkovny (výdejní místo nebo adresa) a osobního odběru.</p><p>Platit můžeš kartou online, QR platbou / převodem nebo na dobírku (+39 Kč). Nad 2 500 Kč máš dopravu zdarma.</p>"],
            ['Vrácení a reklamace', 'vraceni-a-reklamace', 'nakup', "<p>Zboží můžeš do 30 dnů vrátit bez udání důvodu. Pokud nepasuje do auta, které jsi zadal, vrácení je zdarma.</p><p>Reklamaci vyřídíš přes kontaktní formulář nebo e-mail.</p>"],
            ['Obchodní podmínky', 'obchodni-podminky', 'nakup', "<p><strong>Toto je školní projekt.</strong> Obchodní podmínky jsou pouze ukázkové a e-shop ve skutečnosti neprodává.</p><p>Odesláním objednávky kupující potvrzuje, že se seznámil s podmínkami…</p>"],
            ['Ochrana osobních údajů', 'ochrana-osobnich-udaju', 'nakup', "<p>Osobní údaje zpracováváme jen pro vyřízení objednávky a vedení účtu. <strong>Školní projekt – nevyplňuj skutečné údaje.</strong></p>"],
            ['Jak ověřujeme kompatibilitu', 'jak-overujeme-kompatibilitu', 'poradna', "<p>Každý produkt nejdřív zapojíme do reálného auta. Kompatibilitu uvádíme jen u modelů, ve kterých jsme ho sami vyzkoušeli nebo kde nám montáž potvrdilo aspoň pět zákazníků.</p>"],
            ['Potřebné nářadí', 'potrebne-naradi', 'poradna', "<p>Na většinu upgradů stačí plochý šroubovák, plastové páčky a sada Torx bitů. Všechno najdeš v kategorii Nářadí.</p>"],
        ];
        foreach ($pages as $i => [$t, $s, $g, $body]) Page::create(['title' => $t, 'slug' => $s, 'group' => $g, 'body' => $body, 'sort' => $i]);
    }
}
