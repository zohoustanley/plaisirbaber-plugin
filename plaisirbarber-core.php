<?php
/*
Plugin Name: PlaisirBarber Core
Description: Types de contenus et options pour le thème PlaisirBarber (services, équipe, galerie, infos salon).
Version: 1.0.0
Author: Yannick Zohou
Text Domain: plaisirbarber-core
*/

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. CPT : Services, Barbiers, Réalisations (galerie)
 */
add_action('init', 'pbcore_register_post_types');
function pbcore_register_post_types() {

    // Services / Tarifs
    register_post_type('pb_service', [
        'labels' => [
            'name'          => 'Services',
            'singular_name' => 'Service',
            'add_new_item'  => 'Ajouter un service',
            'edit_item'     => 'Modifier le service',
        ],
        'public'       => true,
        'menu_icon'    => 'dashicons-tag',
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes'],
        'has_archive'  => false,
        'show_in_rest' => true,
    ]);

    // Groupes de services (ex: Coiffure Homme & Barbe, Perruques & Tissages)
    register_taxonomy('pb_service_group', 'pb_service', [
        'labels' => [
            'name'          => 'Groupes de services',
            'singular_name' => 'Groupe de services',
            'add_new_item'  => 'Ajouter un groupe',
            'edit_item'     => 'Modifier le groupe',
            'search_items'  => 'Rechercher un groupe',
            'all_items'     => 'Tous les groupes',
        ],
        'public'       => false,          // pas besoin de page publique
        'show_ui'      => true,           // visible dans l’admin
        'show_in_menu' => true,
        'hierarchical' => false,
        'show_tagcloud'=> false,
        'show_admin_column' => true,
    ]);

    // Équipe / Barbiers
    register_post_type('pb_barber', [
        'labels' => [
            'name'          => 'Barbiers',
            'singular_name' => 'Barbier',
            'add_new_item'  => 'Ajouter un barbier',
            'edit_item'     => 'Modifier le barbier',
        ],
        'public'       => true,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes'],
        'has_archive'  => false,
        'show_in_rest' => true,
    ]);

    // Réalisations / Galerie
    register_post_type('pb_work', [
        'labels' => [
            'name'          => 'Réalisations',
            'singular_name' => 'Réalisation',
            'add_new_item'  => 'Ajouter une réalisation',
            'edit_item'     => 'Modifier la réalisation',
        ],
        'public'       => true,
        'menu_icon'    => 'dashicons-format-gallery',
        'supports'     => ['title', 'thumbnail', 'page-attributes'],
        'has_archive'  => false,
        'show_in_rest' => true,
    ]);
}

/**
 * 2. Metaboxes simples : prix du service, rôle du barbier
 */
add_action('add_meta_boxes', 'pbcore_add_meta_boxes');
function pbcore_add_meta_boxes() {
    add_meta_box(
        'pb_service_meta',
        'Détails du service',
        'pbcore_render_service_meta',
        'pb_service',
        'side'
    );

    add_meta_box(
        'pb_barber_meta',
        'Détails du barbier',
        'pbcore_render_barber_meta',
        'pb_barber',
        'side'
    );
}

function pbcore_render_service_meta($post) {
    wp_nonce_field('pbcore_save_service_meta', 'pbcore_service_nonce');
    $price = get_post_meta($post->ID, '_pb_service_price', true);
    ?>
    <p>
        <label for="pb_service_price">Prix (affiché sur la home)</label><br>
        <input type="text" id="pb_service_price" name="pb_service_price" value="<?php echo esc_attr($price); ?>" style="width:100%;" placeholder="Ex : 25 €">
    </p>
    <?php
}

function pbcore_render_barber_meta($post) {
    wp_nonce_field('pbcore_save_barber_meta', 'pbcore_barber_nonce');
    $role = get_post_meta($post->ID, '_pb_barber_role', true);
    ?>
    <p>
        <label for="pb_barber_role">Rôle / fonction</label><br>
        <input type="text" id="pb_barber_role" name="pb_barber_role" value="<?php echo esc_attr($role); ?>" style="width:100%;" placeholder="Ex : Master barber">
    </p>
    <?php
}

add_action('save_post', 'pbcore_save_meta_boxes');
function pbcore_save_meta_boxes($post_id) {

    // Service
    if (isset($_POST['pbcore_service_nonce']) && wp_verify_nonce($_POST['pbcore_service_nonce'], 'pbcore_save_service_meta')) {
        if (isset($_POST['pb_service_price'])) {
            update_post_meta($post_id, '_pb_service_price', sanitize_text_field($_POST['pb_service_price']));
        }
    }

    // Barbier
    if (isset($_POST['pbcore_barber_nonce']) && wp_verify_nonce($_POST['pbcore_barber_nonce'], 'pbcore_save_barber_meta')) {
        if (isset($_POST['pb_barber_role'])) {
            update_post_meta($post_id, '_pb_barber_role', sanitize_text_field($_POST['pb_barber_role']));
        }
    }
}

/**
 * 3. Options globales du salon (hero, contact, réseaux, carte)
 */

add_action('admin_menu', 'pbcore_add_admin_menu');
function pbcore_add_admin_menu() {
    add_menu_page(
        'PlaisirBarber',
        'PlaisirBarber',
        'manage_options',
        'pbcore-settings',
        'pbcore_render_settings_page',
        'dashicons-scissors',
        30
    );
}

function pbcore_get_settings_defaults() {
    return [
        'hero_title'        => "Où tradition rencontre style moderne",
        'hero_subtitle'     => "Barber shop à Plaisir dédié à ceux qui veulent une coupe nette, une barbe soignée et une expérience premium.",
        'hero_cta_label'    => "Prendre rendez-vous",
        'hero_video_url'    => "",

        'address'           => "12 rue du Barber, 78370 Plaisir",
        'phone'             => "01 23 45 67 89",
        'email'             => "contact@plaisirbarber.fr",
        'hours'             => "Mardi – Samedi : 10h–20h",

        'instagram_url'     => "https://www.instagram.com",
        'tiktok_url'        => "https://www.tiktok.com",

        'map_iframe'        => "",
    ];
}

function pbcore_get_settings() {
    $defaults = pbcore_get_settings_defaults();
    $current  = get_option('pbcore_settings', []);
    return wp_parse_args($current, $defaults);
}

function pbcore_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Sauvegarde
    if (isset($_POST['pbcore_save_settings'])) {
        check_admin_referer('pbcore_save_settings');

        $options                  = pbcore_get_settings();
        $options['hero_title']    = sanitize_text_field($_POST['hero_title'] ?? '');
        $options['hero_subtitle'] = sanitize_textarea_field($_POST['hero_subtitle'] ?? '');
        $options['hero_cta_label']= sanitize_text_field($_POST['hero_cta_label'] ?? '');
        $options['hero_video_url']= esc_url_raw($_POST['hero_video_url'] ?? '');

        $options['address']       = sanitize_text_field($_POST['address'] ?? '');
        $options['phone']         = sanitize_text_field($_POST['phone'] ?? '');
        $options['email']         = sanitize_email($_POST['email'] ?? '');
        $options['hours']         = sanitize_text_field($_POST['hours'] ?? '');

        $options['instagram_url'] = esc_url_raw($_POST['instagram_url'] ?? '');
        $options['tiktok_url']    = esc_url_raw($_POST['tiktok_url'] ?? '');

        $options['map_iframe']    = wp_kses_post($_POST['map_iframe'] ?? '');

        update_option('pbcore_settings', $options);

        echo '<div class="updated"><p>Options enregistrées.</p></div>';
    }

    $o = pbcore_get_settings();
    ?>
    <div class="wrap">
        <h1>Réglages PlaisirBarber</h1>

        <form method="post">
            <?php wp_nonce_field('pbcore_save_settings'); ?>

            <h2>Hero (vidéo & texte)</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="hero_title">Titre</label></th>
                    <td><input name="hero_title" id="hero_title" type="text" value="<?php echo esc_attr($o['hero_title']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hero_subtitle">Sous-titre</label></th>
                    <td><textarea name="hero_subtitle" id="hero_subtitle" rows="3" class="large-text"><?php echo esc_textarea($o['hero_subtitle']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hero_cta_label">Texte du bouton</label></th>
                    <td><input name="hero_cta_label" id="hero_cta_label" type="text" value="<?php echo esc_attr($o['hero_cta_label']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hero_video_url">URL de la vidéo (mp4)</label></th>
                    <td><input name="hero_video_url" id="hero_video_url" type="url" value="<?php echo esc_attr($o['hero_video_url']); ?>" class="regular-text">
                        <p class="description">Colle ici l’URL d’une vidéo hébergée dans la médiathèque ou sur un stockage accessible.</p>
                    </td>
                </tr>
            </table>

            <h2>Contact & localisation</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="address">Adresse</label></th>
                    <td><input name="address" id="address" type="text" value="<?php echo esc_attr($o['address']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="phone">Téléphone</label></th>
                    <td><input name="phone" id="phone" type="text" value="<?php echo esc_attr($o['phone']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="email">E-mail</label></th>
                    <td><input name="email" id="email" type="email" value="<?php echo esc_attr($o['email']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="hours">Horaires</label></th>
                    <td><input name="hours" id="hours" type="text" value="<?php echo esc_attr($o['hours']); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>Réseaux sociaux & carte</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="instagram_url">URL Instagram</label></th>
                    <td><input name="instagram_url" id="instagram_url" type="url" value="<?php echo esc_attr($o['instagram_url']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tiktok_url">URL TikTok</label></th>
                    <td><input name="tiktok_url" id="tiktok_url" type="url" value="<?php echo esc_attr($o['tiktok_url']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="map_iframe">Code iframe Google Maps</label></th>
                    <td>
                        <textarea name="map_iframe" id="map_iframe" rows="5" class="large-text"><?php echo esc_textarea($o['map_iframe']); ?></textarea>
                        <p class="description">Colle ici le code &lt;iframe&gt; fourni par Google Maps.</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="pbcore_save_settings" class="button button-primary">Enregistrer</button>
            </p>
        </form>
    </div>
    <?php
}
