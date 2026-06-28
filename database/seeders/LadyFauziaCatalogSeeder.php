<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Model;

class LadyFauziaCatalogSeeder extends Seeder
{
    public function run()
    {
        // Enable mass-assignment protection (reguard) to prevent SQL errors when updating models with custom attributes
        Model::reguard();

        // 1. Truncate existing catalog tables safely
        DB::table('products')->delete();
        DB::table('categories')->where('id', '>', 1)->delete();

        // Re-align category auto-increments
        DB::statement('ALTER TABLE categories AUTO_INCREMENT = 2;');
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1;');

        // Repositories
        $categoryRepository = app(CategoryRepository::class);
        $productRepository = app(ProductRepository::class);

        // 2. Define premium categories and subcategories
        $categoriesConfig = [
            [
                'name' => 'Dresses',
                'slug' => 'dresses',
                'desc' => 'Timeless Modest silhouettes and elegant gowns.',
                'sub' => [
                    ['name' => 'Luxury Dresses', 'slug' => 'luxury-dresses', 'desc' => 'High-end Modest luxury gowns.', 'img' => 'miami_hero.png'],
                    ['name' => 'Kaftans', 'slug' => 'kaftans', 'desc' => 'Flowing silhouettes crafted from premium fabrics.', 'img' => 'kaftan_hero.png'],
                    ['name' => 'Evening Collection', 'slug' => 'evening-collection', 'desc' => 'Stunning silhouettes for your special evening.', 'img' => 'brand_story.png'],
                    ['name' => 'Eid Collection', 'slug' => 'eid-collection', 'desc' => 'Joyful garments designed for Eid celebrations.', 'img' => 'lookbook_teaser.png'],
                    ['name' => 'Wedding Collection', 'slug' => 'wedding-collection', 'desc' => 'Bridal wear and bridal guest attire.', 'img' => 'miami_hero.png'],
                    ['name' => 'Signature Collection', 'slug' => 'signature-collection', 'desc' => 'The ultimate expression of Lady Fauzia.', 'img' => 'about_miami.png'],
                ]
            ],
            [
                'name' => 'Hijabs',
                'slug' => 'hijabs',
                'desc' => 'Premium hijabs in diverse fabrics and styles.',
                'sub' => [
                    ['name' => 'Premium Hijabs', 'slug' => 'premium-hijabs', 'desc' => 'Premium chiffons, silks, and modals.', 'img' => 'hijab_hero.png'],
                    ['name' => 'Everyday Hijabs', 'slug' => 'everyday-hijabs', 'desc' => 'Comfortable cotton and jersey hijabs.', 'img' => 'hijab_hero.png'],
                    ['name' => 'Bridal Hijabs', 'slug' => 'bridal-hijabs', 'desc' => 'Ornate bridal hijabs and lace veils.', 'img' => 'miami_hero.png'],
                    ['name' => 'Crystal Collection', 'slug' => 'crystal-collection', 'desc' => 'Swarovski and hand-beaded crystal hijabs.', 'img' => 'hijab_hero.png'],
                ]
            ],
            [
                'name' => 'Jewelry',
                'slug' => 'jewelry',
                'desc' => 'Exquisite jewelry pieces designed to complement your modest wear.',
                'sub' => [
                    ['name' => 'Earrings', 'slug' => 'earrings', 'desc' => 'Timeless gold and pearl earrings.', 'img' => 'jewelry_hero.png'],
                    ['name' => 'Necklaces', 'slug' => 'necklaces', 'desc' => 'Luxury necklaces and elegant pendants.', 'img' => 'jewelry_hero.png'],
                    ['name' => 'Bracelets', 'slug' => 'bracelets', 'desc' => 'Fine bangles and cuff bracelets.', 'img' => 'jewelry_hero.png'],
                    ['name' => 'Luxury Sets', 'slug' => 'luxury-sets', 'desc' => 'Matching bridal and evening jewelry sets.', 'img' => 'jewelry_hero.png'],
                ]
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'desc' => 'Luxury accessories to complete your look.',
                'sub' => [
                    ['name' => 'Hijab Pins', 'slug' => 'hijab-pins', 'desc' => 'Premium pins and magnetic snaps.', 'img' => 'about_miami.png'],
                    ['name' => 'Luxury Accessories', 'slug' => 'luxury-accessories', 'desc' => 'Fine handbags, cashmere shawls, and silk items.', 'img' => 'about_miami.png'],
                    ['name' => 'Gift Collection', 'slug' => 'gift-collection', 'desc' => 'Curated gift boxes and keepsakes.', 'img' => 'brand_story.png'],
                ]
            ]
        ];

        $categoryMap = [];

        foreach ($categoriesConfig as $catIndex => $cat) {
            $parent = $categoryRepository->create([
                'position' => $catIndex + 1,
                'status' => 1,
                'display_mode' => 'products_and_description',
                'parent_id' => 1,
                'en' => [
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'description' => $cat['desc'],
                ]
            ]);

            foreach ($cat['sub'] as $subIndex => $sub) {
                $child = $categoryRepository->create([
                    'position' => $subIndex + 1,
                    'status' => 1,
                    'display_mode' => 'products_and_description',
                    'parent_id' => $parent->id,
                    'en' => [
                        'name' => $sub['name'],
                        'slug' => $sub['slug'],
                        'description' => $sub['desc'],
                    ]
                ]);

                $categoryMap[$sub['slug']] = $child->id;

                // Copy and attach banner/logo image to the category
                $sourceFile = base_path('packages/Webkul/Installer/src/Resources/assets/images/seeders/products/' . $sub['img']);
                if (file_exists($sourceFile)) {
                    $storedPath = Storage::putFile('category/' . $child->id, new File($sourceFile));
                    $child->update([
                        'logo_path' => $storedPath,
                        'banner_path' => $storedPath,
                    ]);
                }
            }
        }

        // Rebuild nested set category tree
        \Webkul\Category\Models\Category::fixTree();

        // 3. Define products array
        $productsData = [
            // --- Luxury Dresses ---
            [
                'sub_slug' => 'luxury-dresses',
                'sku' => 'ld-amara-emerald',
                'name' => 'The Amara Emerald Satin Gown',
                'price' => 380.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'An elegant emerald green gown crafted from premium silk satin, featuring a draped neckline and structured cuffs.',
                'desc' => "Elegance meets modest luxury in the Amara Satin Gown. Tailored from the finest, heavy-drape silk satin, it radiates a rich emerald sheen. It features a high, sophisticated neckline, subtle shoulder pleating, and a modest A-line silhouette that drapes gracefully to the floor.\n\nMaterial: 100% Premium Silk Satin\nCare: Dry clean only\nHighlights:\n- Rich emerald green hue\n- Hidden back zipper closure\n- Floor-length sweep"
            ],
            [
                'sub_slug' => 'luxury-dresses',
                'sku' => 'ld-seraphina-velvet',
                'name' => 'The Seraphina Velvet Gown',
                'price' => 420.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'A luxurious black velvet gown detailed with delicate hand-sewn gold embroidery along the cuffs and mock collar.',
                'desc' => "Step into luxury with the Seraphina Velvet Gown. Made from ultra-soft, premium velvet, this gown combines plush comfort with royal elegance. The cuffs and mock collar are embellished with intricate gold thread embroidery, meticulously hand-crafted in our Miami atelier.\n\nMaterial: Premium Silk Velvet, Gold Thread\nCare: Professional dry clean\nHighlights:\n- Structured shoulder pads for a tailored look\n- Breathable stretch velvet lining\n- Hand-finished collar detail"
            ],
            [
                'sub_slug' => 'luxury-dresses',
                'sku' => 'ld-dahlia-chiffon',
                'name' => 'The Dahlia Pleated Chiffon Dress',
                'price' => 350.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'A flowing pleated chiffon dress in a soft dusty rose, designed with double lining and a matching satin belt.',
                'desc' => "Crafted for effortless movement, the Dahlia Dress is made from layers of high-grade pleated chiffon. The soft dusty rose shade flatters all skin tones, while the long, balloon sleeves and high collar maintain elegant modesty.\n\nMaterial: 100% Polyester Georgette Chiffon, Satin lining\nCare: Dry clean or delicate hand wash\nHighlights:\n- Includes detachable matching silk-satin belt\n- Elasticized cuffs for styling ease\n- Full inner lining"
            ],

            // --- Kaftans ---
            [
                'sub_slug' => 'kaftans',
                'sku' => 'kf-layla-silk',
                'name' => 'The Layla Embroidered Silk Kaftan',
                'price' => 450.00,
                'image' => 'kaftan_hero.png',
                'short_desc' => 'A timeless silk kaftan in pure ivory, adorned with gold Moroccan-style hand embroidery along the placket.',
                'desc' => "Embody grace and heritage with the Layla Silk Kaftan. Crafted from heavy mulberry silk, this kaftan flows beautifully. The front placket is adorned with gold corded embroidery, creating an elegant editorial look.\n\nMaterial: 100% Mulberry Silk\nCare: Dry clean only\nHighlights:\n- Full-length side slits with inner modest lining\n- Breathable and lightweight feel\n- Handmade details"
            ],
            [
                'sub_slug' => 'kaftans',
                'sku' => 'kf-soraya-brocade',
                'name' => 'The Soraya Brocade Kaftan',
                'price' => 490.00,
                'image' => 'kaftan_hero.png',
                'short_desc' => 'An opulent brocade kaftan featuring gold and copper metallic threads woven into a rich navy canvas.',
                'desc' => "The Soraya Kaftan is a masterwork of textile design, utilizing premium metallic brocade that catches the light with every step. Perfect for evening galas and formal gatherings.\n\nMaterial: Silk-Metallic Brocade Blend\nCare: Dry clean only\nHighlights:\n- Deep navy and gold colorway\n- Elegant bell sleeves\n- Relaxed yet regal drape"
            ],
            [
                'sub_slug' => 'kaftans',
                'sku' => 'kf-yasmin-linen',
                'name' => 'The Yasmin Linen Resort Kaftan',
                'price' => 280.00,
                'image' => 'kaftan_hero.png',
                'short_desc' => 'A relaxed-fit beach resort kaftan in cream, made from organic flax linen with subtle fringe detailing.',
                'desc' => "Designed for summer days and resort getaways, the Yasmin Kaftan is breathable, organic, and effortlessly chic. The side seams are finished with delicate, hand-knotted cotton fringes.\n\nMaterial: 100% Organic Flax Linen\nCare: Machine wash cold, hang dry\nHighlights:\n- Lightweight and highly breathable\n- Side pockets\n- Semi-casual style"
            ],

            // --- Evening Collection ---
            [
                'sub_slug' => 'evening-collection',
                'sku' => 'ev-noor-sequin',
                'name' => 'The Noor Sequined Evening Gown',
                'price' => 550.00,
                'image' => 'brand_story.png',
                'short_desc' => 'A dazzling midnight blue gown covered in micro-sequins, featuring a high neck and elegant trailing cape.',
                'desc' => "Be the center of attention in the Noor Evening Gown. Thousands of midnight blue micro-sequins are sewn onto a soft mesh base, creating a subtle, starry shimmer. A dramatic chiffon cape drapes from the shoulders.\n\nMaterial: Sequin-embellished Mesh, Chiffon Cape\nCare: Dry clean only\nHighlights:\n- Attached shoulder cape\n- Full jersey lining for ultimate comfort\n- Invisible zip back closure"
            ],
            [
                'sub_slug' => 'evening-collection',
                'sku' => 'ev-zaria-jacquard',
                'name' => 'The Zaria Jacquard Evening Dress',
                'price' => 580.00,
                'image' => 'brand_story.png',
                'short_desc' => 'An structured evening dress made from textured gold floral jacquard, featuring elegant bishop sleeves.',
                'desc' => "The Zaria Evening Dress stands out with its structured silhouette and luxurious jacquard weave. Gold and champagne threads create a beautiful floral relief pattern on a structured fabric.\n\nMaterial: Premium Floral Jacquard\nCare: Dry clean only\nHighlights:\n- Elegant bishop sleeves with gold buttons\n- Tailored bodice and flared skirt\n- High mock neck design"
            ],
            [
                'sub_slug' => 'evening-collection',
                'sku' => 'ev-samira-wrap',
                'name' => 'The Samira Satin Wrap Gown',
                'price' => 410.00,
                'image' => 'brand_story.png',
                'short_desc' => 'A sophisticated bronze satin wrap gown featuring a side tie closure and elegant drape.',
                'desc' => "The Samira Wrap Gown is all about clean lines and luxury draping. Made from thick, heavy-weight bronze satin, it wraps securely and drapes into a flattering A-line silhouette.\n\nMaterial: Heavy-weight Polyester Satin\nCare: Hand wash cold or dry clean\nHighlights:\n- Rich bronze metallic tone\n- True wrap styling with interior secure buttons\n- Elastic cuffs"
            ],

            // --- Eid Collection ---
            [
                'sub_slug' => 'eid-collection',
                'sku' => 'ed-medina-organza',
                'name' => 'The Medina Festivities Organza Set',
                'price' => 390.00,
                'image' => 'lookbook_teaser.png',
                'short_desc' => 'A premium three-piece Eid set featuring a sheer floral organza jacket, satin inner slip, and wide-leg trousers.',
                'desc' => "Perfect for Eid celebrations, the Medina Set offers versatile, high-end layering. The sheer organza jacket is embroidered with delicate floral motifs and paired with matching luxury trousers.\n\nMaterial: Silk Organza, Polyester Satin\nCare: Dry clean only\nHighlights:\n- Three-piece coordinating set\n- Elastic-back wide trousers\n- Elegant pastel blue shade"
            ],
            [
                'sub_slug' => 'eid-collection',
                'sku' => 'ed-farah-eid',
                'name' => 'The Farah Embroidered Eid Dress',
                'price' => 340.00,
                'image' => 'lookbook_teaser.png',
                'short_desc' => 'A charming lavender Eid dress featuring hand-embroidered floral vines along the sleeves and hemline.',
                'desc' => "Bring joy to your holiday wardrobe with the Farah Dress. Made from soft, breathable modal-cotton blend, it is designed for all-day comfort during Eid celebrations.\n\nMaterial: 60% Modal, 40% Cotton, Satin embroidery\nCare: Gentle wash cold\nHighlights:\n- Soft lavender hue\n- Matching fabric belt included\n- Detailed floral embroidery"
            ],
            [
                'sub_slug' => 'eid-collection',
                'sku' => 'ed-safiya-georgette',
                'name' => 'The Safiya Georgette Gown',
                'price' => 370.00,
                'image' => 'lookbook_teaser.png',
                'short_desc' => 'A double-layered mint green georgette gown featuring a pleated waist and gold-trimmed sleeves.',
                'desc' => "The Safiya Gown features a refreshing mint green tone. High-quality georgette creates a lightweight, flowing skirt, complemented by detailed gold trim on the wrist cuffs.\n\nMaterial: Premium Georgette, Silk lining\nCare: Dry clean recommended\nHighlights:\n- Intricate gold braid details on cuffs\n- Flattering pleated waistband\n- Fully lined"
            ],

            // --- Wedding Collection ---
            [
                'sub_slug' => 'wedding-collection',
                'sku' => 'wd-aliyah-bridal',
                'name' => 'The Aliyah Satin Bridal Gown',
                'price' => 1200.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'A breathtaking modest bridal gown in luxury ivory silk satin, featuring a pearl-beaded high collar and a 2-meter train.',
                'desc' => "Make your special day unforgettable in the Aliyah Bridal Gown. Crafted from heavy, high-luster silk satin, it features a hand-beaded high collar with freshwater pearls. The structured design flares into a spectacular cathedral train.\n\nMaterial: 100% Bridal Silk Satin, Freshwater Pearls\nCare: Professional bridal dry clean only\nHighlights:\n- Hand-beaded collar and cuffs\n- Built-in inner plateau\n- 2-meter trailing train"
            ],
            [
                'sub_slug' => 'wedding-collection',
                'sku' => 'wd-mariam-lace',
                'name' => 'The Mariam Lace Wedding Gown',
                'price' => 1500.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'An exquisite bridal gown overlayed in premium French lace with hand-sewn crystal embellishments and satin sash.',
                'desc' => "Intricate French lace meets absolute modesty in the Mariam Gown. Featuring full-length lace sleeves, a scalloped mock neck, and delicate crystal embellishments that glimmer in the light.\n\nMaterial: French Chantilly Lace, Satin Lining\nCare: Professional dry clean\nHighlights:\n- Hand-applied Swarovski crystal accents\n- High collar design\n- Includes satin sash"
            ],
            [
                'sub_slug' => 'wedding-collection',
                'sku' => 'wd-laila-pearl',
                'name' => 'The Laila Pearl Embellished Gown',
                'price' => 980.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'A regal wedding guest gown in soft champagne, decorated with a cascading pearl pattern across the shoulders.',
                'desc' => "Crafted for the sophisticated wedding guest, the Laila Gown is made from a heavy champagne crepe that resists creasing. The shoulders are hand-beaded with cascading glass pearls.\n\nMaterial: Luxury Crepe, Glass Pearls\nCare: Dry clean only\nHighlights:\n- Soft champagne colorway\n- Structured, modest silhouette\n- Hand-beaded pearl detailing"
            ],

            // --- Signature Collection ---
            [
                'sub_slug' => 'signature-collection',
                'sku' => 'sg-fauzia-signature',
                'name' => 'The Fauzia Signature Silk Gown',
                'price' => 620.00,
                'image' => 'about_miami.png',
                'short_desc' => 'Our flagship gown in rich royal gold, crafted from hand-loomed mulberry silk with gold embroidery.',
                'desc' => "The absolute pinnacle of Lady Fauzia design. This flagship piece is crafted from premium mulberry silk, custom dyed in royal gold. Embroidered with signature motifs designed in our Miami studio.\n\nMaterial: 100% Hand-loomed Mulberry Silk\nCare: Dry clean only\nHighlights:\n- Premium branded signature piece\n- Intricate gold embroidery\n- Exquisite editorial drape"
            ],
            [
                'sub_slug' => 'signature-collection',
                'sku' => 'sg-miami-breeze',
                'name' => 'The Miami Breeze Resort Dress',
                'price' => 310.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A chic resort dress in pure white silk chiffon, reflecting the sunny elegance of Miami.',
                'desc' => "Inspired by our Miami launch, this dress features double-layered white silk chiffon that catches the wind. It keeps you cool and elegant under the sun.\n\nMaterial: 100% Silk Chiffon, Viscose lining\nCare: Hand wash cold\nHighlights:\n- Inspired by Miami, FL\n- Light, airy feel\n- Fully lined"
            ],
            [
                'sub_slug' => 'signature-collection',
                'sku' => 'sg-zeina-silk',
                'name' => 'The Zeina Silk Draped Gown',
                'price' => 480.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A stunning teal draped gown made from premium satin silk, featuring asymmetrical modest pleats.',
                'desc' => "The Zeina Gown features architectural draping and pleating. Made from deep teal satin silk, it creates a sleek, premium, and sophisticated look.\n\nMaterial: 100% Satin Silk\nCare: Dry clean only\nHighlights:\n- Unique asymmetrical draping\n- Deep teal hue\n- Floor-length sweep"
            ],

            // --- Premium Hijabs ---
            [
                'sub_slug' => 'premium-hijabs',
                'sku' => 'hj-aria-chiffon',
                'name' => 'The Aria Premium Chiffon Hijab',
                'price' => 35.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A luxury Georgette chiffon hijab in soft beige, offering a lightweight feel and secure hold.',
                'desc' => "Crafted from high-grade Georgette chiffon, the Aria Hijab offers a beautiful drape, textured hold, and long-lasting quality.\n\nMaterial: Premium Georgette Chiffon\nDimensions: 180cm x 70cm\nCare: Hand wash cold\nHighlights:\n- Non-slip texture\n- Breathable weave"
            ],
            [
                'sub_slug' => 'premium-hijabs',
                'sku' => 'hj-celine-silk',
                'name' => 'The Celine Premium Silk Hijab',
                'price' => 45.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'An opulent mulberry silk hijab in charcoal grey, featuring a soft satin sheen.',
                'desc' => "Add high-end luxury to your outfit with the Celine Silk Hijab. Made from pure mulberry silk, it is lightweight, breathable, and incredibly soft.\n\nMaterial: 100% Mulberry Silk\nDimensions: 175cm x 65cm\nCare: Hand wash cold or dry clean\nHighlights:\n- Elegant satin sheen\n- Hypoallergenic material"
            ],
            [
                'sub_slug' => 'premium-hijabs',
                'sku' => 'hj-daria-modal',
                'name' => 'The Daria Premium Modal Hijab',
                'price' => 38.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A luxury modal hijab in soft taupe, highly breathable and naturally wrinkle-resistant.',
                'desc' => "The Daria Modal Hijab is perfect for premium, everyday comfort. Made from natural beechwood fibers, it is incredibly soft and holds its shape.\n\nMaterial: 100% Premium Modal\nDimensions: 180cm x 75cm\nCare: Machine wash cold on delicate\nHighlights:\n- Naturally wrinkle-resistant\n- Soft modal texture"
            ],

            // --- Everyday Hijabs ---
            [
                'sub_slug' => 'everyday-hijabs',
                'sku' => 'hj-everyday-cotton',
                'name' => 'The Everyday Cotton Crinkle Hijab',
                'price' => 22.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A versatile, light crinkle cotton hijab in tan, perfect for everyday style.',
                'desc' => "Our signature crinkle cotton hijab offers maximum styling convenience. It requires no ironing and features a soft, textured crinkle pattern.\n\nMaterial: 100% Organic Cotton\nDimensions: 185cm x 80cm\nCare: Cold wash\nHighlights:\n- No ironing required\n- Breathable fabric"
            ],
            [
                'sub_slug' => 'everyday-hijabs',
                'sku' => 'hj-everyday-jersey',
                'name' => 'The Everyday Jersey Stretch Hijab',
                'price' => 25.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A stretch cotton-jersey hijab in neutral black, offering full coverage and stay-in-place comfort.',
                'desc' => "Crafted from medium-weight stretch cotton jersey, this hijab stays in place all day without pins. Highly breathable and sweat-wicking.\n\nMaterial: 95% Cotton, 5% Spandex Jersey\nDimensions: 180cm x 70cm\nCare: Machine wash cold\nHighlights:\n- Four-way stretch\n- Stay-in-place comfort"
            ],
            [
                'sub_slug' => 'everyday-hijabs',
                'sku' => 'hj-everyday-viscose',
                'name' => 'The Everyday Premium Viscose Hijab',
                'price' => 24.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A lightweight viscose hijab in soft blush pink, offering a smooth touch and graceful drape.',
                'desc' => "The perfect blend of viscose fibers makes this hijab lightweight and smooth, draping elegantly over the shoulders.\n\nMaterial: 100% Premium Viscose\nDimensions: 180cm x 75cm\nCare: Hand wash cold\nHighlights:\n- Light and airy drape\n- Beautiful blush pink hue"
            ],

            // --- Bridal Hijabs ---
            [
                'sub_slug' => 'bridal-hijabs',
                'sku' => 'hj-aliyah-bridal',
                'name' => 'The Aliyah Satin Bridal Hijab',
                'price' => 85.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'An elegant ivory bridal hijab designed to match our Aliyah Bridal Gown, featuring pearl borders.',
                'desc' => "Specifically crafted to pair with our bridal gown, this hijab features a heavy ivory silk satin body and is finished with hand-beaded freshwater pearls along the front edge.\n\nMaterial: 100% Bridal Silk Satin, Freshwater Pearls\nDimensions: 180cm x 70cm\nCare: Professional dry clean only\nHighlights:\n- Hand-beaded pearl border\n- Opulent bridal finish"
            ],
            [
                'sub_slug' => 'bridal-hijabs',
                'sku' => 'hj-mariam-lace',
                'name' => 'The Mariam Lace Bridal Veil Hijab',
                'price' => 95.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'A delicate bridal veil hijab trimmed in French Chantilly lace, matching the Mariam Gown.',
                'desc' => "An ethereal bridal accessory. A sheer organza chiffon body is framed by a beautiful French Chantilly lace border, adding romance and modesty to the bridal look.\n\nMaterial: Premium Silk Chiffon, French Lace\nDimensions: 200cm x 75cm\nCare: Dry clean only\nHighlights:\n- Scalloped lace border\n- Extra length for veil draping"
            ],
            [
                'sub_slug' => 'bridal-hijabs',
                'sku' => 'hj-zahra-bridal',
                'name' => 'The Zahra Embroidered Bridal Hijab',
                'price' => 75.00,
                'image' => 'miami_hero.png',
                'short_desc' => 'A white bridal chiffon hijab detailed with silver embroidery and glass bead embellishments.',
                'desc' => "Embellish your wedding day style with the Zahra Hijab. Silver threads are woven into delicate patterns, highlighted with hand-applied glass beads.\n\nMaterial: Silk Chiffon, Silver Thread, Glass Beads\nDimensions: 180cm x 70cm\nCare: Professional dry clean\nHighlights:\n- Shimmering silver details\n- Hand-beaded edges"
            ],

            // --- Crystal Collection ---
            [
                'sub_slug' => 'crystal-collection',
                'sku' => 'hj-amira-crystal',
                'name' => 'The Amira Crystal Embellished Hijab',
                'price' => 65.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A premium black georgette hijab featuring a border of scattered luxury crystals.',
                'desc' => "Elevate your evening look with the Amira Crystal Hijab. High-grade crystals are heat-set along the border, giving a beautiful shine without adding weight.\n\nMaterial: Premium Georgette Chiffon, Heat-set Crystals\nDimensions: 180cm x 70cm\nCare: Hand wash cold inside out\nHighlights:\n- Scattered crystal design\n- Excellent drape"
            ],
            [
                'sub_slug' => 'crystal-collection',
                'sku' => 'hj-leyla-swarovski',
                'name' => 'The Leyla Swarovski Trim Hijab',
                'price' => 120.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A luxury silk-chiffon hijab embellished with a border of genuine Swarovski crystals.',
                'desc' => "A stunning luxury accessory. Hand-finished in our Miami studio, genuine Swarovski crystals are set along the edges to create a brilliant, high-end shimmer.\n\nMaterial: 100% Silk Chiffon, Genuine Swarovski Crystals\nDimensions: 180cm x 70cm\nCare: Dry clean only\nHighlights:\n- Genuine Swarovski crystals\n- Exquisite silk touch"
            ],
            [
                'sub_slug' => 'crystal-collection',
                'sku' => 'hj-soraya-beaded',
                'name' => 'The Soraya Hand-Beaded Crystal Hijab',
                'price' => 90.00,
                'image' => 'hijab_hero.png',
                'short_desc' => 'A navy blue chiffon hijab featuring hand-sewn crystals and glass bugle beads.',
                'desc' => "Each Soraya Hijab is hand-beaded by our artisans, spending over 4 hours on each piece. Glass beads and crystals form a beautiful floral pattern at the crown.\n\nMaterial: Premium Chiffon, Glass Beads, Crystals\nDimensions: 180cm x 70cm\nCare: Hand wash cold or dry clean\nHighlights:\n- Artisanal hand-beaded pattern\n- Deep navy shade"
            ],

            // --- Earrings ---
            [
                'sub_slug' => 'earrings',
                'sku' => 'jw-miami-blossom',
                'name' => 'The Miami Blossom Gold Earrings',
                'price' => 180.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'Elegant flower-shaped studs in 18k gold-plated brass, finished with a central freshwater pearl.',
                'desc' => "Celebrate our Miami launch with these floral-inspired earrings. Shaped like orange blossoms and plated in rich 18k gold, they are centered with a premium freshwater pearl.\n\nMaterial: 18k Gold Plated Brass, Freshwater Pearl\nCare: Avoid moisture, clean with soft cloth\nHighlights:\n- Flower design\n- Hypoallergenic posts"
            ],
            [
                'sub_slug' => 'earrings',
                'sku' => 'jw-oasis-dew',
                'name' => 'The Oasis Dew Drop Earrings',
                'price' => 150.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'Minimalist drop earrings featuring teardrop-cut emerald crystals suspended from gold chains.',
                'desc' => "Add a touch of color with these elegant drop earrings. Teardrop emerald-green crystals hang from delicate 18k gold chains, catching light with every movement.\n\nMaterial: 18k Gold Plated Brass, Emerald Green Crystal\nLength: 4cm drop\nHighlights:\n- Teardrop emerald accent\n- Lightweight feel"
            ],
            [
                'sub_slug' => 'earrings',
                'sku' => 'jw-crescent-pearl',
                'name' => 'The Crescent Moon Pearl Earrings',
                'price' => 210.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'Statement hoop earrings shaped like crescent moons, decorated with tiny seed pearls.',
                'desc' => "Timeless crescent moon shapes are lined with tiny, hand-set natural seed pearls. Plated in 18k gold for a beautiful, long-lasting finish.\n\nMaterial: 18k Gold Plated Sterling Silver, Seed Pearls\nHighlights:\n- Intricate seed pearl inlay\n- Classic crescent motif"
            ],

            // --- Necklaces ---
            [
                'sub_slug' => 'necklaces',
                'sku' => 'jw-fauzia-choker',
                'name' => 'The Fauzia Signature Choker Necklace',
                'price' => 320.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A statement choker necklace featuring interlocking gold links and a central brand crest.',
                'desc' => "Our signature jewelry piece. This thick choker necklace features hand-polished interlocking links and a central Lady Fauzia brand crest medallion.\n\nMaterial: 18k Gold Plated Brass\nLength: 38cm with 5cm extender\nHighlights:\n- Flagship signature necklace\n- Interlocking link design"
            ],
            [
                'sub_slug' => 'necklaces',
                'sku' => 'jw-medina-lapis',
                'name' => 'The Medina Lapis Lazuli Pendant',
                'price' => 250.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A gold pendant necklace featuring a polished round lapis lazuli stone, framed in gold cord work.',
                'desc' => "The deep blue lapis lazuli stone represents wisdom and truth. Framed in a gold cord-like setting, it hangs from a durable 18k gold-filled rope chain.\n\nMaterial: 18k Gold Filled, Natural Lapis Lazuli\nHighlights:\n- Deep blue natural gemstone\n- Rope chain"
            ],
            [
                'sub_slug' => 'necklaces',
                'sku' => 'jw-sahara-gold',
                'name' => 'The Sahara Sand Gold Necklace',
                'price' => 280.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A multi-layered chain necklace featuring textured gold discs that mimic Sahara sands.',
                'desc' => "Three layered gold chains are decorated with textured gold discs. It creates a beautiful layer look that pairs perfectly with high necklines.\n\nMaterial: 18k Gold Plated Sterling Silver\nHighlights:\n- Pre-layered design\n- Textured disc details"
            ],

            // --- Bracelets ---
            [
                'sub_slug' => 'bracelets',
                'sku' => 'jw-miami-sunset',
                'name' => 'The Miami Sunset Gold Bangle',
                'price' => 190.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A sleek, structured gold bangle detailed with engraved sunrays and a secure clasp.',
                'desc' => "This sleek bangle is engraved with detailed sunray motifs, inspired by Miami beach sunsets. Plated in 18k gold over sterling silver.\n\nMaterial: 18k Gold Plated Sterling Silver\nSize: 17cm circumference\nHighlights:\n- Sunray engravings\n- Secure hinge clasp"
            ],
            [
                'sub_slug' => 'bracelets',
                'sku' => 'jw-seraphina-cuff',
                'name' => 'The Seraphina Emerald Cuff Bracelet',
                'price' => 340.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A flexible open cuff bracelet tipped with two hexagonal-cut emerald stones.',
                'desc' => "Designed for effortless wear, this open cuff bracelet is flexible and easily adjusts to fit the wrist. Tipped with two green emerald stones in bezel settings.\n\nMaterial: 18k Gold Plated Brass, Hexagonal Emerald Crystals\nHighlights:\n- Flexible slip-on design\n- Deep green crystal accents"
            ],
            [
                'sub_slug' => 'bracelets',
                'sku' => 'jw-jasmine-pearl',
                'name' => 'The Jasmine Pearl Chain Bracelet',
                'price' => 160.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A delicate chain bracelet interspersed with natural freshwater pearls and gold beads.',
                'desc' => "A delicate and timeless accessory. Fine freshwater pearls alternate with polished gold beads along a thin cable chain.\n\nMaterial: 18k Gold Filled, Natural Pearls\nHighlights:\n- Cable chain with lobster clasp\n- Natural freshwater pearls"
            ],

            // --- Luxury Sets ---
            [
                'sub_slug' => 'luxury-sets',
                'sku' => 'jw-aliyah-set',
                'name' => 'The Aliyah Pearl Bridal Jewelry Set',
                'price' => 850.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A matching three-piece set including a pearl drop necklace, drop earrings, and a chain bracelet.',
                'desc' => "The complete bridal jewelry set. Made from high-luster freshwater pearls and 18k white gold-plated sterling silver. Perfect for brides and formal wear.\n\nMaterial: 18k White Gold Plated Silver, Freshwater Pearls\nIncludes: Necklace, Earrings, Bracelet\nHighlights:\n- Gift boxed\n- Perfect bridal coordinates"
            ],
            [
                'sub_slug' => 'luxury-sets',
                'sku' => 'jw-mariam-set',
                'name' => 'The Mariam Diamond Cut Luxury Set',
                'price' => 1200.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A luxurious necklace and earring set set with high-grade diamond-cut cubic zirconia.',
                'desc' => "Dazzle in our Mariam Luxury Set. Hundreds of diamond-cut cubic zirconia crystals are set by hand in sterling silver, creating a brilliant sparkle.\n\nMaterial: Rhodium Plated Sterling Silver, Cubic Zirconia\nIncludes: Collar Necklace, Drop Earrings\nHighlights:\n- Brilliant diamond-cut crystals\n- Hypoallergenic metals"
            ],
            [
                'sub_slug' => 'luxury-sets',
                'sku' => 'jw-samira-set',
                'name' => 'The Samira Emerald Gold Jewelry Set',
                'price' => 950.00,
                'image' => 'jewelry_hero.png',
                'short_desc' => 'A matching necklace and bracelet set featuring oval-cut emerald crystals framed in gold.',
                'desc' => "Designed to complement our Samira Wrap Gown, this jewelry set features deep green oval-cut crystals surrounded by polished gold work.\n\nMaterial: 18k Gold Plated Sterling Silver, Emerald Green Crystals\nIncludes: Pendant Necklace, Link Bracelet\nHighlights:\n- Deep green emerald hue\n- Interlocking gold link frames"
            ],

            // --- Hijab Pins ---
            [
                'sub_slug' => 'hijab-pins',
                'sku' => 'ac-pearl-pins',
                'name' => 'The Premium Pearl Hijab Pin Set',
                'price' => 25.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A set of three premium straight pins tipped with high-quality freshwater pearls.',
                'desc' => "Secure your hijab in style. This set includes three ultra-sharp straight pins tipped with natural white, peach, and lavender pearls.\n\nMaterial: Stainless Steel Pins, Freshwater Pearls\nIncludes: 3 straight pins\nHighlights:\n- Snag-free design\n- Natural pearl tips"
            ],
            [
                'sub_slug' => 'hijab-pins',
                'sku' => 'ac-magnet-pins',
                'name' => 'The Crystal Magnet Hijab Pin Set',
                'price' => 30.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A set of two pairs of ultra-strong magnetic hijab pins finished with crystal bezels.',
                'desc' => "Secure your hijab without damaging delicate fabrics like silk or chiffon. These ultra-strong magnets are finished with crystal bezels.\n\nMaterial: Neodymium Magnets, Crystal Bezels\nIncludes: 2 pairs of magnets\nHighlights:\n- Fabric-safe magnetic closure\n- Sparkly crystal finish"
            ],
            [
                'sub_slug' => 'hijab-pins',
                'sku' => 'ac-signature-pins',
                'name' => 'The Gold Plated Signature Pin Set',
                'price' => 45.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A set of three gold-plated straight pins featuring the Lady Fauzia signature logo mark.',
                'desc' => "Add brand details to your styling with our gold-plated straight pins, featuring our signature circular LF logo crest.\n\nMaterial: 18k Gold Plated Brass\nIncludes: 3 logo pins\nHighlights:\n- Signature brand crest detail\n- Sturdy stainless steel post"
            ],

            // --- Luxury Accessories ---
            [
                'sub_slug' => 'luxury-accessories',
                'sku' => 'ac-leather-handbag',
                'name' => 'The Lady Fauzia Leather Handbag',
                'price' => 450.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A luxury top-handle handbag in pebbled cream leather, featuring a gold logo clasp.',
                'desc' => "The perfect luxury companion. Crafted from Italian pebbled leather in cream, it features structured handles, an adjustable shoulder strap, and a custom gold-plated logo clasp.\n\nMaterial: 100% Italian Calf Leather, Gold Plated Hardware\nDimensions: 22cm x 18cm x 10cm\nHighlights:\n- Custom gold logo lock\n- Microfiber suede lining"
            ],
            [
                'sub_slug' => 'luxury-accessories',
                'sku' => 'ac-scrunchie-set',
                'name' => 'The Silk Satin Hair Scrunchie Set',
                'price' => 35.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A set of three pure silk-satin scrunchies designed to protect hair under your hijab.',
                'desc' => "Protect your hair from breakage. These scrunchies are made from pure mulberry silk, offering a smooth touch that won't pull or snag hair.\n\nMaterial: 100% Mulberry Silk\nIncludes: 3 scrunchies (Cream, Taupe, Bronze)\nHighlights:\n- Protects hair from friction\n- Gentle elastic core"
            ],
            [
                'sub_slug' => 'luxury-accessories',
                'sku' => 'ac-cashmere-shawl',
                'name' => 'The Miami Breeze Wool Cashmere Shawl',
                'price' => 180.00,
                'image' => 'about_miami.png',
                'short_desc' => 'A warm, premium wool-cashmere blend shawl in soft ivory, perfect for cool evenings.',
                'desc' => "Wrap yourself in luxury. Made from a premium wool and cashmere blend, this shawl is incredibly soft, lightweight, and warm.\n\nMaterial: 70% Wool, 30% Cashmere\nDimensions: 200cm x 80cm\nHighlights:\n- Wool cashmere blend\n- Light fringe details"
            ],

            // --- Gift Collection ---
            [
                'sub_slug' => 'gift-collection',
                'sku' => 'ac-gift-box',
                'name' => 'The Signature Luxury Gift Box Set',
                'price' => 250.00,
                'image' => 'brand_story.png',
                'short_desc' => 'A curated gift box containing a silk scarf, signature candle, and gold pin set.',
                'desc' => "The perfect luxury gift. This beautifully boxed set includes a pure silk scarf, a signature soy candle, and our gold-plated signature pin set.\n\nMaterial: Silk, Glass, Gold Plating\nIncludes: Silk Scarf, Soy Candle, 3 Pins\nHighlights:\n- Deluxe branded packaging\n- Hand-tied satin bow"
            ],
            [
                'sub_slug' => 'gift-collection',
                'sku' => 'ac-gift-card',
                'name' => 'The Modest Elegance E-Gift Card',
                'price' => 100.00,
                'image' => 'brand_story.png',
                'short_desc' => 'An digital gift card for Lady Fauzia, delivered instantly via email.',
                'desc' => "Give the gift of choice. Our E-Gift Card is delivered instantly via email and can be redeemed for any product on our online storefront.\n\nType: Digital Gift Card\nDelivery: Instant Email\nHighlights:\n- Available in multiple denominations\n- Never expires"
            ],
            [
                'sub_slug' => 'gift-collection',
                'sku' => 'ac-keepsake-box',
                'name' => 'The Ramadan Mubarak Keepsake Box',
                'price' => 150.00,
                'image' => 'brand_story.png',
                'short_desc' => 'A decorative wooden keepsake box filled with organic dates and premium hijab pins.',
                'desc' => "Celebrate holy moments. A handcrafted wooden box with carved geometric patterns, containing a selection of premium organic dates and a set of crystal hijab magnets.\n\nMaterial: Carved Walnut Wood, Organic dates, Neodymium magnets\nIncludes: Carved Box, Dates, Magnet set\nHighlights:\n- Hand-carved details\n- Limited holiday edition"
            ]
        ];

        foreach ($productsData as $prod) {
            $catId = $categoryMap[$prod['sub_slug']] ?? null;
            if (!$catId) continue;

            // Create product model entry
            $product = $productRepository->create([
                'type' => 'simple',
                'attribute_family_id' => 1,
                'sku' => $prod['sku'],
            ]);

            // Update product attributes, channel, category, inventory
            $productRepository->update([
                'sku' => $prod['sku'],
                'name' => $prod['name'],
                'url_key' => Str::slug($prod['name']),
                'price' => $prod['price'],
                'weight' => 1.0,
                'status' => 1,
                'visible_individually' => 1,
                'short_description' => $prod['short_desc'],
                'description' => $prod['desc'],
                'categories' => [$catId],
                'channels' => [1],
                'inventories' => [
                    1 => 100
                ]
            ], $product->id);

            // Copy and attach product image
            $sourceFile = base_path('packages/Webkul/Installer/src/Resources/assets/images/seeders/products/' . $prod['image']);
            if (file_exists($sourceFile)) {
                $storedPath = Storage::putFile('product/' . $product->id, new File($sourceFile));
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'path' => $storedPath,
                    'position' => 1,
                ]);
            }
        }
    }
}
