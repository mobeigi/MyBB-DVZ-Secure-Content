<?php
/* by Tomasz 'Devilshakerz' Mlynski [devilshakerz.com]; Copyright (C) 2015-2017
 released under Creative Commons BY-NC-SA 4.0 license: https://creativecommons.org/licenses/by-nc-sa/4.0/ */

$l['setting_group_dvz_secure_content'] = 'DVZ Secure Content';
$l['setting_group_dvz_secure_content_desc'] = 'Settings for DVZ Secure Content.';

$l['setting_dvz_sc_filter_insecure_images'] = 'Filter non-HTTPS MyCode images';
$l['setting_dvz_sc_filter_insecure_images_desc'] = 'Prevent displaying non-HTTPS MyCode images by replacing them with links when <i>Image proxy</i> is not used.';

$l['setting_dvz_sc_block_insecure_avatars'] = 'Block non-HTTPS avatars';
$l['setting_dvz_sc_block_insecure_avatars_desc'] = 'Require remote avatars to be linked to over HTTPS.';

$l['setting_dvz_sc_proxy'] = 'Image proxy';
$l['setting_dvz_sc_proxy_desc'] = 'Forward resource requests to a proxy server.';

$l['setting_dvz_sc_proxy_scheme'] = 'Image proxy URL scheme';
$l['setting_dvz_sc_proxy_scheme_desc'] = 'Template used to construct the resulting resource URL. You can use <b>{PROXY_URL}</b>, <b>{URL}</b>, and <b>{DIGEST}</b>.';

$l['setting_dvz_sc_proxy_url'] = 'Image proxy URL';
$l['setting_dvz_sc_proxy_url_desc'] = 'Image proxy URL containing the protocol, domain and a trailing slash. Used for removing and blocking insecure resources.';

$l['setting_dvz_sc_proxy_key'] = 'Image proxy key';
$l['setting_dvz_sc_proxy_key_desc'] = 'Key to be used when creating the URL digest.';

$l['setting_dvz_sc_proxy_digest_algorithm'] = 'Image proxy digest algorithm';
$l['setting_dvz_sc_proxy_digest_algorithm_desc'] = 'Algorithm used in digest generation.';

$l['setting_dvz_sc_proxy_url_protocol'] = 'Image proxy forwarded URL protocol';
$l['setting_dvz_sc_proxy_url_protocol_desc'] = 'Modifies the protocol part of the forwarded URL.';
$l['setting_dvz_sc_proxy_url_protocol_raw'] = 'No changes';
$l['setting_dvz_sc_proxy_url_protocol_relative'] = 'Protocol-relative form';
$l['setting_dvz_sc_proxy_url_protocol_strip'] = 'Strip protocol';

$l['setting_dvz_sc_proxy_url_encoding'] = 'Image proxy forwarded URL encoding';
$l['setting_dvz_sc_proxy_url_encoding_desc'] = 'Encodes the URL of requested image in given format.';
$l['setting_dvz_sc_proxy_url_encoding_raw'] = 'No encoding';
$l['setting_dvz_sc_proxy_url_encoding_percent'] = 'Percent-encoding (urlencode)';
$l['setting_dvz_sc_proxy_url_encoding_rfc1738'] = 'RFC 1738 (rawurlencode)';
$l['setting_dvz_sc_proxy_url_encoding_hex'] = 'Hex encoding';
$l['setting_dvz_sc_proxy_url_encoding_base64'] = 'base64 encoding';
$l['setting_dvz_sc_proxy_url_encoding_base64url'] = 'base64url encoding';

$l['setting_dvz_sc_proxy_images'] = 'Image proxy policy';
$l['setting_dvz_sc_proxy_images_desc'] = 'Forward selected types of images in user content through the proxy server.';
$l['setting_dvz_sc_proxy_images_all'] = 'All images (HTTP & HTTPS)';
$l['setting_dvz_sc_proxy_images_insecure'] = 'Insecure images only (HTTP)';
$l['setting_dvz_sc_proxy_images_none'] = 'Don\'t forward images';

$l['setting_dvz_sc_proxy_avatars'] = 'Proxy avatars';
$l['setting_dvz_sc_proxy_avatars_desc'] = 'Forward selected types of images in user avatars through the proxy server.';
$l['setting_dvz_sc_proxy_avatars_all'] = 'All images (HTTP & HTTPS)';
$l['setting_dvz_sc_proxy_avatars_insecure'] = 'Insecure images only (HTTP)';
$l['setting_dvz_sc_proxy_avatars_none'] = 'Don\'t forward images';

$l['dvz_sc_task_embed_templates'] = 'Secure MyCode video templates';
$l['dvz_sc_task_embed_templates_message'] = 'MyCode embedded video templates have been modified to use HTTPS (links only if not available).';
$l['dvz_sc_task_embed_templates_revert'] = 'Revert changes to MyCode video templates';
$l['dvz_sc_task_embed_templates_revert_message'] = 'Changes to MyCode video templates have been reverted.';
$l['dvz_sc_task_replace_gravatar'] = 'Secure Gravatar avatars';
$l['dvz_sc_task_replace_gravatar_message'] = 'Gravatar avatars have been modified to use HTTPS.';
$l['dvz_sc_task_remove_insecure_avatars'] = 'Remove non-HTTPS remote avatars';
$l['dvz_sc_task_remove_insecure_avatars_message'] = 'Non-HTTPS remote avatars have been removed.';
$l['dvz_sc_task_proxy_avatar_urls'] = 'Proxy avatar URLs';
$l['dvz_sc_task_proxy_avatar_urls_message'] = 'Avatar URLs have been saved with routing through the proxy.';
$l['dvz_sc_task_restore_proxy_avatar_urls'] = 'Restore avatar URLs';
$l['dvz_sc_task_restore_proxy_avatar_urls_message'] = 'Original avatar URLs have been restored.';

$l['dvz_sc_controls_dynamic'] = 'Security overwiew - dynamic';
$l['dvz_sc_controls_resources'] = 'Security overview - existing resources';

$l['dvz_sc_status_images'] = 'MyCode images HTTPS only';
$l['dvz_sc_status_avatars'] = 'New avatars HTTPS only';
$l['dvz_sc_status_secure_avatars'] = 'All remote avatars using HTTPS';
$l['dvz_sc_status_avatars_proxied'] = 'All remote avatars proxied';
$l['dvz_sc_status_secure_embed_templates'] = 'Default MyCode video embeds using HTTPS';
$l['dvz_sc_status_proxy_all'] = 'All remote resources proxied';

$l['dvz_sc_status_yes'] = 'Yes';
$l['dvz_sc_status_no'] = 'No';
