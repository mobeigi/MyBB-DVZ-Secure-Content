<?php
/* by Tomasz 'Devilshakerz' Mlynski [devilshakerz.com]; Copyright (C) 2015-2017
 released under Creative Commons BY-NC-SA 4.0 license: https://creativecommons.org/licenses/by-nc-sa/4.0/ */

$plugins->add_hook('parse_message', ['dvz_sc', 'parse_message']);
$plugins->add_hook('usercp_avatar_start', ['dvz_sc', 'usercp_avatar_start']);
$plugins->add_hook('usercp_avatar_intermediate', ['dvz_sc', 'usercp_avatar_intermediate']);
$plugins->add_hook('usercp_do_avatar_start', ['dvz_sc', 'usercp_do_avatar_start']);
$plugins->add_hook('usercp_do_avatar_end', ['dvz_sc', 'usercp_do_avatar_end']);
$plugins->add_hook('modcp_do_editprofile_end', ['dvz_sc', 'modcp_do_editprofile_end']);
$plugins->add_hook('admin_user_users_edit_graph', ['dvz_sc', 'admin_user_users_edit_graph']);
$plugins->add_hook('admin_user_users_edit', ['dvz_sc', 'admin_user_users_edit']);
$plugins->add_hook('admin_user_users_edit_commit_start', ['dvz_sc', 'admin_user_users_edit_commit_start']);
$plugins->add_hook('admin_config_settings_begin', ['dvz_sc', 'admin_config_settings_begin']);
$plugins->add_hook('admin_settings_print_peekers', ['dvz_sc', 'admin_settings_print_peekers']);
$plugins->add_hook('admin_config_plugins_begin', ['dvz_sc', 'admin_config_plugins_begin']);

function dvz_secure_content_info()
{
    return [
        'name'           => 'DVZ Secure Content',
        'description'    => 'Filters and forwards user-generated content from insecure protocols (non-HTTPS).' . dvz_sc::description_appendix(),
        'website'        => 'https://devilshakerz.com/',
        'author'         => 'Tomasz \'Devilshakerz\' Mlynski',
        'authorsite'     => 'https://devilshakerz.com/',
        'version'        => '1.1.5',
        'codename'       => 'dvz_secure_content',
        'compatibility'  => '18*',
    ];
}

function dvz_secure_content_install()
{
    global $db, $cache;

    // database changes
    $db->modify_column('users', 'avatar', "VARCHAR(255) NOT NULL DEFAULT ''");

    if (!$db->field_exists('avatar_original', 'users')) {
        $db->add_column('users', 'avatar_original', "VARCHAR(255) NOT NULL DEFAULT ''");
    }

    // settings
    $settingGroupId = $db->insert_query('settinggroups', [
        'name'        => 'dvz_secure_content',
        'title'       => 'DVZ Secure Content',
        'description' => 'Settings for DVZ Secure Content.',
    ]);

    $settings = [
        [
            'name'        => 'dvz_sc_filter_insecure_images',
            'title'       => 'Filter non-HTTPS MyCode images',
            'description' => 'Prevent displaying non-HTTPS MyCode images by replacing them with links when <i>Image proxy</i> is not used.',
            'optionscode' => 'onoff',
            'value'       => '1',
        ],
        [
            'name'        => 'dvz_sc_block_insecure_avatars',
            'title'       => 'Block non-HTTPS avatars',
            'description' => 'Require remote avatars to be linked to over HTTPS.',
            'optionscode' => 'onoff',
            'value'       => '1',
        ],
        [
            'name'        => 'dvz_sc_proxy',
            'title'       => 'Image proxy',
            'description' => 'Forward resource requests to a proxy server.',
            'optionscode' => 'onoff',
            'value'       => '0',
        ],
        [
            'name'        => 'dvz_sc_proxy_scheme',
            'title'       => 'Image proxy URL scheme',
            'description' => 'Template used to construct the resulting resource URL. You can use <b>{PROXY_URL}</b>, <b>{URL}</b>, and <b>{DIGEST}</b>.',
            'optionscode' => 'text',
            'value'       => '{PROXY_URL}{DIGEST}/{URL}',
        ],
        [
            'name'        => 'dvz_sc_proxy_url',
            'title'       => 'Image proxy URL',
            'description' => 'Image proxy URL containing the protocol, domain and a trailing slash. Used for removing and blocking insecure resources.',
            'optionscode' => 'text',
            'value'       => 'https://example.com/',
        ],
        [
            'name'        => 'dvz_sc_proxy_key',
            'title'       => 'Image proxy key',
            'description' => 'Key to be used when creating the URL digest.',
            'optionscode' => 'text',
            'value'       => '',
        ],
        [
            'name'        => 'dvz_sc_proxy_digest_algorithm',
            'title'       => 'Image proxy digest algorithm',
            'description' => 'Algorithm used in digest generation.',
            'optionscode' => 'text',
            'value'       => 'sha1',
        ],
        [
            'name'        => 'dvz_sc_proxy_url_protocol',
            'title'       => 'Image proxy forwarded URL protocol',
            'description' => 'Modifies the protocol part of the forwarded URL.',
            'optionscode' => 'select
raw=No changes
relative=Protocol-relative form
strip=Strip protocol
',
            'value'       => 'raw',
        ],
        [
            'name'        => 'dvz_sc_proxy_url_encoding',
            'title'       => 'Image proxy forwarded URL encoding',
            'description' => 'Encodes the URL of requested image in given format.',
            'optionscode' => 'select
raw=No encoding
percent=Percent-encoding (urlencode)
rfc1738=RFC 1738 (rawurlencode)
hex=Hex encoding
base64=base64 encoding
base64url=base64url encoding
',
            'value'       => 'hex',
        ],
        [
            'name'        => 'dvz_sc_proxy_images',
            'title'       => 'Image proxy policy',
            'description' => 'Forward selected types of images in user content through the proxy server.',
            'optionscode' => 'select
all=All images (HTTP & HTTPS)
insecure=Insecure images only (HTTP)
none=Don\'t forward images',
            'value'       => 'all',
        ],
        [
            'name'        => 'dvz_sc_proxy_avatars',
            'title'       => 'Proxy avatars',
            'description' => 'Forward selected types of images in user avatars through the proxy server.',
            'optionscode' => 'select
all=All images (HTTP & HTTPS)
insecure=Insecure images only (HTTP)
none=Don\'t forward images',
            'value'       => 'all',
        ],
    ];

    $i = 1;

    foreach ($settings as &$row) {
        array_walk($row, function (&$value) use ($db) {
            $value = $db->escape_string($value);
        });
        $row['gid']       = $settingGroupId;
        $row['disporder'] = $i++;
    }

    $db->insert_query_multiple('settings', $settings);

    rebuild_settings();
}

function dvz_secure_content_uninstall()
{
    global $db;

    // database
    if ($db->field_exists('avatar_original', 'users')) {
        $proxyUrls = $db->fetch_field(
            $db->simple_select('users', 'COUNT(uid) AS n', "avatar_original != ''"),
            'n'
        );

        if ($proxyUrls == 0) {
            $db->drop_column('users', 'avatar_original');
        }
    }

    // settings
    $settingGroupId = $db->fetch_field(
        $db->simple_select('settinggroups', 'gid', "name='dvz_secure_content'"),
        'gid'
    );

    $db->delete_query('settinggroups', 'gid=' . $settingGroupId);
    $db->delete_query('settings', 'gid=' . $settingGroupId);

    rebuild_settings();
}

function dvz_secure_content_is_installed()
{
    global $db;

    // manual check to avoid caching issues
    $query = $db->simple_select('settinggroups', 'gid', "name='dvz_secure_content'");
    return (bool)$db->num_rows($query);
}

class dvz_sc
{
    static $showPluginTools = false;

    static $videoEmbedServices = [
        'metacafe',
        'veoh',
    ];

    static $originalAvatarUrl = null;

    // hooks
    static function parse_message(&$message)
    {
        global $mybb, $parser;

        if (!isset($parser) || !$parser instanceof postParser || $parser->options['allow_imgcode']) {

            if (self::settings('filter_insecure_images') || (self::settings('proxy') && self::settings('proxy_images') != 'none')) {

                $protocol = 'http' . (self::settings('proxy') && self::settings('proxy_images') == 'all' ? 's?' : null) . ':\/\/';
                $pattern = '/<img src="(' . $protocol . '[^<>"\']+)"((?: width="[0-9]+" height="[0-9]+")?(?: border="0")? alt="([^<>"\']+)" ?(?: style="float: (left|right);")?(?: class="mycode_img")? ?)\/>/i';

                $message = preg_replace_callback($pattern, 'self::parse_message_replace_callback', $message);

            }

        }
    }

    static function usercp_avatar_start()
    {
        global $mybb, $lang, $footer;

        if (self::settings('block_insecure_avatars')) {

            $lang->load('dvz_secure_content');

            $lang->avatar_url_note .= $lang->dvz_sc_avatars_https_only;

            // client-side form validation (simplified match for e-mail addresses and ^https:// URLs)
            $footer .= PHP_EOL . '<script>$("input[name=\'avatarurl\']").attr("pattern", ".+@.+|https://.+")</script>';

        }
    }

    static function usercp_avatar_intermediate()
    {
        global $mybb, $avatarurl;

        // insert original URL for the form after the display URL has been fetched
        if (!empty($mybb->user['avatar_original'])) {
            $avatarurl = $mybb->user['avatar_original'];
        }
    }

    static function usercp_do_avatar_start()
    {
        global $mybb, $db;

        $avatarUrl = trim($mybb->get_input('avatarurl'));

        if (!empty($mybb->input['remove'])) {
            // clean up the backup field
            if (!empty($mybb->user['avatar_original'])) {
                $db->update_query('users', [
                    'avatar_original' => '',
                ], "uid=" . (int)$mybb->user['uid']);
            }
        // verify the core is going to process an avatar
        } elseif (
            empty($_FILES['avatarupload']['name']) &&
            $mybb->settings['allowremoteavatars']
        ) {
            if (self::settings('block_insecure_avatars') && !self::is_secure_url($avatarUrl)) {
                $mybb->input['avatarurl'] = ''; // force core to reject the request
            } elseif (self::settings('proxy_avatars') != 'none') {
                if (self::settings('proxy_images') == 'all') {
                    // treat Gravatar images as ordinary ones to proxy the URLs
                    $mybb->input['avatar_url'] = self::gravatar_email_to_url($mybb->input['avatar_url']);
                }

                if (self::settings('proxy_avatars') == 'all' || !self::is_secure_url($avatarUrl)) {
                    // inject a proxied URL before the resource is fetched
                    $mybb->input['avatarurl'] = self::proxy_url($avatarUrl);

                    // cancel request if data too long for storage
                    if (mb_strlen($mybb->input['avatarurl']) > 255) {
                        $mybb->input['avatarurl'] = ''; // force core to reject the request
                    } else {
                        self::$originalAvatarUrl = $avatarUrl;
                    }
                }
            }
        }
    }

    static function usercp_do_avatar_end()
    {
        global $mybb, $db;

        // reflect changes in the backup field / clean up
        if (
            self::$originalAvatarUrl ||
            (!self::settings('proxy_avatars') && !empty($mybb->user['avatar_original']))
        ) {

            $db->update_query('users', [
                'avatar_original' => self::$originalAvatarUrl ? self::$originalAvatarUrl : '',
            ], "uid=" . (int)$mybb->user['uid']);

        }
    }

    static function modcp_do_editprofile_end()
    {
        global $mybb, $db, $user;

        // clean up the backup field
        if (!empty($mybb->input['remove_avatar'])) {
            if (!empty($user['avatar_original'])) {
                $db->update_query('users', [
                    'avatar_original' => '',
                ], "uid=" . (int)$user['uid']);
            }

        }
    }

    static function admin_user_users_edit_graph()
    {
        global $user;

        // insert original URL for the form
        if (!empty($user['avatar']) && !empty($user['avatar_original'])) {
            echo '<script>$("#avatar_url").val("' . htmlspecialchars_uni($user['avatar_original']) . '");</script>';
        }
    }

    static function admin_user_users_edit()
    {
        global $mybb, $user;

        // manipulate data if the avatar proxy policy is active
        if (self::settings('proxy_avatars') != 'none') {
            // verify the core is going to process an avatar
            if (
                !$_FILES['avatar_upload']['name'] &&
                !empty($mybb->input['avatar_url']) &&
                $mybb->settings['allowremoteavatars']
            ) {
                if (self::settings('proxy_images') == 'all') {
                    // treat Gravatar images as ordinary ones to proxy the URLs
                    $mybb->input['avatar_url'] = self::gravatar_email_to_url($mybb->input['avatar_url']);
                }

                // verify the core is going to process a remote avatar
                if (
                    $mybb->input['avatar_url'] != $user['avatar_original'] &&
                    filter_var($mybb->input['avatar_url'], FILTER_VALIDATE_EMAIL) === false
                ) {
                    $avatarUrl = $mybb->input['avatar_url'];

                    if (self::settings('proxy_avatars') == 'all' || !self::is_secure_url($avatarUrl)) {
                        // inject a proxied URL before the resource is fetched
                        $mybb->input['avatar_url'] = self::proxy_url($avatarUrl);

                        // leave out data if too long for storage
                        if (mb_strlen($mybb->input['avatar_url']) > 255) {
                            $mybb->input['avatar_url'] = '';
                        } else {
                            self::$originalAvatarUrl = $avatarUrl;
                        }
                    }
                }
            }
        }
    }

    static function admin_user_users_edit_commit_start()
    {
        global $mybb, $extra_user_updates;

        if (
            !empty($mybb->input['remove_avatar']) ||
            self::settings('proxy_avatars') == 'none' && !empty($user['avatar_original'])
        ) {
            // clean up the backup field
            $extra_user_updates['avatar_original'] = '';
        }

        if (self::$originalAvatarUrl) {
            // reflect changes in the backup field
            $extra_user_updates['avatar_original'] = self::$originalAvatarUrl;
        }
    }

    static function admin_config_settings_begin()
    {
        global $lang;
        $lang->load('dvz_secure_content');
    }

    static function admin_config_plugins_begin()
    {
        global $mybb, $lang;

        self::$showPluginTools = true;

        $lang->load('dvz_secure_content');

        if ($mybb->get_input('dvz_sc_task_embed_templates') && verify_post_check($mybb->get_input('my_post_key'))) {
            self::replace_embed_templates(true);
            flash_message($lang->dvz_sc_task_embed_templates_message, 'success');
            admin_redirect('index.php?module=config-plugins');
        }

        if ($mybb->get_input('dvz_sc_task_embed_templates_revert') && verify_post_check($mybb->get_input('my_post_key'))) {
            self::replace_embed_templates(false);
            flash_message($lang->dvz_sc_task_embed_templates_revert_message, 'success');
            admin_redirect('index.php?module=config-plugins');
        }

        if ($mybb->get_input('dvz_sc_task_replace_gravatar') && verify_post_check($mybb->get_input('my_post_key'))) {
            self::replace_gravatar_avatars();
            flash_message($lang->dvz_sc_task_replace_gravatar_message, 'success');
            admin_redirect('index.php?module=config-plugins');
        }

        if ($mybb->get_input('dvz_sc_task_remove_insecure_avatars') && verify_post_check($mybb->get_input('my_post_key'))) {
            self::remove_insecure_avatars();
            flash_message($lang->dvz_sc_task_remove_insecure_avatars_message, 'success');
            admin_redirect('index.php?module=config-plugins');
        }

        if ($mybb->get_input('dvz_sc_task_proxy_avatar_urls') && verify_post_check($mybb->get_input('my_post_key'))) {
            self::proxy_avatar_urls();
            flash_message($lang->dvz_sc_task_proxy_avatar_urls_message, 'success');
            admin_redirect('index.php?module=config-plugins');
        }

        if ($mybb->get_input('dvz_sc_task_restore_proxy_avatar_urls') && verify_post_check($mybb->get_input('my_post_key'))) {
            self::restore_proxy_avatar_urls();
            flash_message($lang->dvz_sc_task_restore_proxy_avatar_urls_message, 'success');
            admin_redirect('index.php?module=config-plugins');
        }

    }


    static function admin_settings_print_peekers($peekers)
    {
        $peekerSettings = [
            'dvz_sc_proxy_scheme',
            'dvz_sc_proxy_url',
            'dvz_sc_proxy_key',
            'dvz_sc_proxy_digest_algorithm',
            'dvz_sc_proxy_url_protocol',
            'dvz_sc_proxy_url_encoding',
            'dvz_sc_proxy_images',
            'dvz_sc_proxy_avatars',
        ];

        return array_merge($peekers, [
            'new Peeker($(".setting_dvz_sc_proxy"), $("#row_setting_' . implode($peekerSettings, ', #row_setting_') . '"), 1, true);',
        ]);
    }

    // tasks
    static function replace_embed_templates($secureMode)
    {
        require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';

        foreach (self::$videoEmbedServices as $service) {

            $skipMatching = false;

            switch ($service) {

                // replace embeds with link
                case 'metacafe':
                    $replace = [
                        '<iframe src="http://www.metacafe.com/embed/{$id}/" width="440" height="248" allowFullScreen frameborder=0></iframe>',
                        '<a href="http://www.metacafe.com/fplayer/{$id}/{$title}.swf">[metacafe.com/...]</a>',
                    ];
                    $skipMatching = true;
                    break;

                case 'veoh':
                    $replace = [
                        '<object width="410" height="341" id="veohFlashPlayer" name="veohFlashPlayer"><param name="movie" value="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1446&permalinkId={$id}&player=videodetailsembedded&videoAutoPlay=0&id=anonymous"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1446&permalinkId={$id}&player=videodetailsembedded&videoAutoPlay=0&id=anonymous" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="410" height="341" id="veohFlashPlayerEmbed" name="veohFlashPlayerEmbed"></embed></object>',
                        '<a href="http://www.veoh.com/swf/webplayer/WebPlayer.swf?version=AFrontend.5.7.0.1446&permalinkId={$id}&player=videodetailsembedded&videoAutoPlay=0&id=anonymous">[veoh.com/...]</a>',
                    ];
                    $skipMatching = true;
                    break;

                // set embeds protocol-relative
                default:
                    $replace = [
                        '"http://',
                        '"//',
                    ];

            }

            if (!$secureMode) {
                $replace = array_reverse($replace);
            }

            find_replace_templatesets('video_' . $service . '_embed', '#' . ($skipMatching ? '^.*$' : preg_quote($replace[0])) . '#', $replace[1]);

        }
    }

    static function replace_gravatar_avatars()
    {
        global $db;
        return $db->write_query("UPDATE " . TABLE_PREFIX . "users SET avatar = REPLACE(LOWER(avatar), 'http://www.gravatar.com/', 'https://gravatar.com/')");
    }

    static function remove_insecure_avatars()
    {
        global $db;
        return $db->update_query(
            'users',
            [
                'avatar'           => '',
                'avatartype'       => '',
                'avatardimensions' => '',
            ],
            "LOWER(avatar) LIKE 'http://%'"
        );
    }

    static function proxy_avatar_urls()
    {
        global $mybb, $db;

        $query = $db->simple_select('users', 'uid,avatar,avatar_original', "
            avatar != '' AND
            (LOWER(avatar) LIKE 'http://%' OR LOWER(avatar) LIKE 'https://%') AND
            avatar NOT LIKE '" . $db->escape_string_like(self::settings('proxy_url')) . "%'
        ");

        while ($row = $db->fetch_array($query)) {
            if (mb_strpos($row['avatar_original'], 'http://') === 0 || mb_strpos($row['avatar_original'], 'https://') === 0) {
                $originalUrl = $row['avatar_original'];
            } else {
                $originalUrl = $row['avatar'];
            }

            if (mb_strpos($originalUrl, self::settings('proxy_url')) === 0) {
                $proxiedUrl = $originalUrl;
            } else {
                $proxiedUrl = self::proxy_url($originalUrl);
            }

            if (mb_strlen($url) > 255) {
                $db->update_query('users', [
                    'avatar' => '',
                    'avatar_original' => $originalUrl,
                    'avatartype' => '',
                ], "uid=" . (int)$row['uid']);
            } else {
                $db->update_query('users', [
                    'avatar' => $proxiedUrl,
                    'avatar_original' => $originalUrl,
                    'avatartype' => 'remote',
                ], "uid=" . (int)$row['uid']);
            }
        }
    }

    static function restore_proxy_avatar_urls()
    {
        global $db;
        return $db->write_query("UPDATE " . $db->table_prefix . "users SET avatar=avatar_original, avatar_original='', avatartype='remote' WHERE avatar_original != ''");
    }

    // core
    static function parse_message_replace_callback($matches)
    {
        global $mybb;

        if (self::settings('proxy') && self::settings('proxy_url')) {

            $url = self::proxy_url($matches[1]);
            $replacement = '<img src="' . $url . '"' . $matches[2] . '/>';

        } else {
            if (isset($GLOBALS['parser']) && $GLOBALS['parser'] instanceof postParser) {
                $parser = $GLOBALS['parser'];
            } else {
                $parser = new postParser;
                $parser->options = [
                    'allow_mycode' => 0,
                    'allow_smilies' => 0,
                    'allow_imgcode' => 0,
                    'allow_html' => 0,
                    'filter_badwords' => 0,
                ];
            }

            $replacement = $parser->mycode_parse_url($matches[1], $matches[3]);
        }

        return $replacement;
    }

    static function existing_avatars_secure()
    {
        global $db;
        return $db->fetch_field(
            $db->simple_select('users', 'COUNT(uid) AS n', "avatar LIKE 'http://%'"),
            'n'
        ) == 0;
    }

    static function existing_avatars_proxied()
    {
        global $db;

        if (!self::settings('proxy_url')) {
            return false;
        }

        return $db->fetch_field(
            $db->simple_select('users', 'COUNT(uid) AS n', "
                avatar != '' AND
                (LOWER(avatar) LIKE 'http://%' OR LOWER(avatar) LIKE 'https://%') AND
                avatar NOT LIKE '" . $db->escape_string_like(self::settings('proxy_url')) . "%'
            "),
            'n'
        ) == 0;
    }

    static function embed_templates_secure()
    {
        global $db;

        $templatesetsInUse = [];

        $query = $db->simple_select('themes', 'properties', "tid > 1");

        while ($theme = $db->fetch_array($query)) {
            $properties = my_unserialize($theme['properties']);
            $templatesetsInUse[] = (int)$properties['templateset'];
        }

        $templatesetsInUse = array_unique($templatesetsInUse);

        $numTemplatesetsInUse = count($templatesetsInUse);

        $templatesFound = 0;

        if ($numTemplatesetsInUse > 0) {

            $titles = self::$videoEmbedServices;

            array_walk($titles, function (&$title) use ($db) {
                $title = "'" . $db->escape_string('video_' . $title . '_embed') . "'";
            });

            $query = $db->simple_select('templates', 'title,template,sid', "title IN (" . implode(',', $titles) . ") AND sid IN (" . implode(',', $templatesetsInUse) . ")");

            while ($template = $db->fetch_array($query)) {

                if (preg_match('#(?<!<a href=")http://#', $template['template'])) {
                    return false;
                }

                $templatesFound++;
            }

            if ($templatesFound == count(self::$videoEmbedServices) * $numTemplatesetsInUse) {
                return true;
            }

        }

        return false;
    }

    static function is_secure_url($url)
    {
        return mb_strpos($url, 'https://') === 0;
    }

    static function proxy_url($url)
    {
        global $mybb;

        if (mb_strpos($url, self::settings('proxy_url')) !== 0) {

            $passedUrl = $url;

            switch (self::settings('proxy_url_protocol')) {
                case 'strip':
                    if (mb_strpos($passedUrl, 'http://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 7);
                    } elseif (mb_strpos($passedUrl, 'https://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 8);
                    }
                    break;
                case 'relative':
                    if (mb_strpos($passedUrl, 'http://') === 0) {
                        $passedUrl = mb_substr($passedUrl, 4);
                    } elseif (mb_strpos($passedUrl, 'https://')) {
                        $passedUrl = mb_substr($passedUrl, 5);
                    }
                    break;
                default:
                    $passedUrl = $url;
                    break;
            }

            switch (self::settings('proxy_url_encoding')) {
                case 'hex':
                    $passedUrl = bin2hex($passedUrl);
                    break;
                case 'percent':
                    $passedUrl = urlencode($passedUrl);
                    break;
                case 'rfc1738':
                    $passedUrl = rawurlencode($passedUrl);
                    break;
                case 'base64':
                    $passedUrl = base64_encode($passedUrl);
                    break;
                case 'base64url':
                    $passedUrl = rtrim(strtr(base64_encode($passedUrl), '+/', '-_'), '=');
                    break;
                default:
                    $passedUrl = $passedUrl;
                    break;
            }

            if (self::settings('proxy_key')) {
                $digest = hash_hmac(self::settings('proxy_digest_algorithm'), $url, self::settings('proxy_key'));
            } else {
                $digest = null;
            }

            $proxyUrl = self::settings('proxy_scheme');

            $proxyUrl = str_replace('{PROXY_URL}', self::settings('proxy_url'), $proxyUrl);
            $proxyUrl = str_replace('{URL}', $passedUrl, $proxyUrl);
            $proxyUrl = str_replace('{DIGEST}', $digest, $proxyUrl);

        } else {
            $proxyUrl = $url;
        }

        return $proxyUrl;
    }

    static function gravatar_email_to_url($string)
    {
        global $mybb;

        $url = $string;

        if (filter_var($string, FILTER_VALIDATE_EMAIL) !== false) {
            $email = md5(strtolower(trim($string)));
            $s = '';

            if (!$mybb->settings['maxavatardims']) {
                $mybb->settings['maxavatardims'] = '100x100';
            }

            $maxwidth = reset(explode('x', my_strtolower($mybb->settings['maxavatardims'])));
            $s = '?s=' . $maxwidth;

            $url = 'https://www.gravatar.com/avatar/' . $email . $s;
        }

        return $url;
    }

    static function description_appendix()
    {
        global $mybb, $lang;

        $content = null;

        if (self::$showPluginTools) {

            $avatarsSecure = self::existing_avatars_secure();
            $avatarsProxied = self::existing_avatars_proxied();
            $embedsSecure = self::embed_templates_secure();

            $controls = [
                'dynamic' => [
                    'name' => $lang->dvz_sc_controls_dynamic,
                    'controls' => [
                        [
                            'title'  => $lang->dvz_sc_status_images,
                            'status' => self::settings('filter_insecure_images') || (self::settings('proxy') && self::settings('proxy_images') != 'none'),
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_avatars,
                            'status' => self::settings('block_insecure_avatars') || (self::settings('proxy') && self::settings('proxy_avatars') != 'none'),
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_proxy_all,
                            'status' => self::settings('proxy') && self::settings('proxy_images') == 'all' && self::settings('proxy_avatars') == 'all',
                        ],
                    ],
                ],
                'resources' => [
                    'name' => $lang->dvz_sc_controls_resources,
                    'controls' => [
                        [
                            'title'  => $lang->dvz_sc_status_secure_avatars,
                            'status' => $avatarsSecure,
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_avatars_proxied,
                            'status' => $avatarsProxied,
                        ],
                        [
                            'title'  => $lang->dvz_sc_status_secure_embed_templates,
                            'status' => $embedsSecure,
                        ],
                    ],
                ]
            ];

            $content .= '<br /><br />';

            foreach ($controls as $controlGroup) {
                $content .= '<strong>' . $controlGroup['name'] . '</strong><br />';
                foreach ($controlGroup['controls'] as $control) {
                    $content .= ' <span style="display: inline-block; margin: 2px 0; padding: 4px; background-color:' . ($control['status'] ? 'mediumseagreen' : 'lightslategray') . '; font-size: 9px; color: #FFF">' . $control['title'] . ': ' . ($control['status'] ? $lang->dvz_sc_status_yes : $lang->dvz_sc_status_no) . '</span>';
                }
                $content .= '<br />';
            }

            $taskLinks = [];

            if (!$embedsSecure) {
                $taskLinks['dvz_sc_task_embed_templates'] = $lang->dvz_sc_task_embed_templates;
            } else {
                $taskLinks['dvz_sc_task_embed_templates_revert'] = $lang->dvz_sc_task_embed_templates_revert;
            }

            if (!$avatarsSecure) {
                $taskLinks['dvz_sc_task_replace_gravatar'] = $lang->dvz_sc_task_replace_gravatar;
                $taskLinks['dvz_sc_task_remove_insecure_avatars'] = $lang->dvz_sc_task_remove_insecure_avatars;
            }

            if (self::settings('proxy') && self::settings('proxy_avatars') != 'none') {
                $taskLinks['dvz_sc_task_proxy_avatar_urls'] = $lang->dvz_sc_task_proxy_avatar_urls;
                $taskLinks['dvz_sc_task_restore_proxy_avatar_urls'] = $lang->dvz_sc_task_restore_proxy_avatar_urls;
            }

            foreach ($taskLinks as $task => $title) {
                $content .= '<br />&bull; <a href="index.php?module=config-plugins&amp;' . $task . '=1&amp;my_post_key=' . $mybb->post_code . '"><strong>' . $title . '</strong></a>';
            }

            $content .= '<br />';

        }

        return $content;
    }

    static function settings($name)
    {
        global $mybb;
        return $mybb->settings['dvz_sc_' . $name];
    }

}
