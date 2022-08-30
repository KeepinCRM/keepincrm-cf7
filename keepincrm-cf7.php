<?php
/*
 * Plugin Name: KeepinCRM connector for ContactForm7
 * Plugin URI:  https://keepincrm.com
 * Description: This plugin connects ContactForm7 with KeepinCRM via Webhook
 * Author: KeepinCRM
 * Author URI: https://keepincrm.com
 * Version: 1.2.0
 */

add_action('wpcf7_before_send_mail', 'keepincrm_before_send_mail');
function keepincrm_before_send_mail($contact_form)
{
    $properties = $contact_form->prop('ctz_keepincrm');

    if (empty($properties) || empty($properties['activate']) || empty($properties['webhook_url'])) {
        return false;
    }

    $submission = WPCF7_Submission::get_instance();
    $posted_data = $submission->get_posted_data();

    $webhook_url = $properties['webhook_url'];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $webhook_url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($posted_data));
    curl_exec($curl);
    curl_close($curl);
}

add_filter('wpcf7_editor_panels', 'editor_panels');
function editor_panels($panels)
{
    $panels['keepincrm-panel'] = array(
        'title'       => 'KeepinCRM',
        'callback'    => 'keepincrm_panel_html',
    );

    return $panels;
}

function keepincrm_panel_html(WPCF7_ContactForm $contactform)
{
    $activate = '0';
    $webhook_url = '';

    if (is_a($contactform, 'WPCF7_ContactForm')) {
        $properties = $contactform->prop('ctz_keepincrm');

        if (isset($properties['activate'])) {
            $activate = $properties['activate'];
        }

        if (isset($properties['webhook_url'])) {
            $webhook_url = $properties['webhook_url'];
        }
    }
?>
    <fieldset>
        <legend>Webhook URL знаходиться в налаштуваннях інтеграцій в <a href="https://keepincrm.com">KeepinCRM</a></legend>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label>Інтеграція</label>
                    </th>
                    <td>
                        <p>
                            <label for="ctz_keepincrm_activate">
                                <input type="checkbox" id="ctz_keepincrm_activate" name="ctz_keepincrm_activate" value="1" <?php checked($activate, "1") ?>>
                                Увімкнути відправку даних в KeepinCRM
                            </label>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Webhook URL</label>
                    </th>
                    <td>
                        <p>
                            <label for="ctz_keepincrm_webhook_url">
                                <input type="url" id="ctz_keepincrm_webhook_url" name="ctz_keepincrm_webhook_url" value="<?php echo $webhook_url; ?>" style="width: 100%;">
                            </label>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </fieldset>
<?php
}

add_action('wpcf7_save_contact_form', 'keepincrm_save_contact_form');
function keepincrm_save_contact_form($contact_form)
{
    $new_properties = array();

    if (isset($_POST['ctz_keepincrm_activate']) && $_POST['ctz_keepincrm_activate'] == '1') {
        $new_properties['activate'] = '1';
    } else {
        $new_properties['activate'] = '0';
    }

    if (isset($_POST['ctz_keepincrm_webhook_url'])) {
        $new_properties['webhook_url'] = esc_url_raw($_POST['ctz_keepincrm_webhook_url']);
    } else {
        $new_properties['webhook_url'] = '';
    }

    $properties = $contact_form->get_properties();
    $old_properties = $properties['ctz_keepincrm'];
    $properties['ctz_keepincrm'] = array_merge($old_properties, $new_properties);
    $contact_form->set_properties($properties);
}

add_filter('wpcf7_contact_form_properties', 'keepincrm_contact_form_properties', 10, 2);
add_filter('wpcf7_pre_construct_contact_form_properties', 'keepincrm_contact_form_properties', 10, 2);
function keepincrm_contact_form_properties($properties, $instance)
{
    if (!isset($properties['ctz_keepincrm'])) {
        $properties['ctz_keepincrm'] = array(
            'activate'            => '0',
            'webhook_url'         => '',
        );
    }

    return $properties;
}
