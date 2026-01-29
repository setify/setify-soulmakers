<?php
/**
 * Mail Class
 *
 * Handles email sending functionality with custom Soulmakers template
 *
 * @package Soulmakers
 * @since   1.0.0
 */

// Direkten Zugriff verhindern
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Soulmakers_Mail
 */
class Soulmakers_Mail {

    /**
     * Instance
     *
     * @var Soulmakers_Mail|null
     */
    private static ?Soulmakers_Mail $instance = null;

    /**
     * Allowed HTML tags for email content
     *
     * @var array
     */
    private array $allowed_html = array(
        'h1'     => array(),
        'h2'     => array(),
        'h3'     => array(),
        'p'      => array(),
        'br'     => array(),
        'strong' => array(),
        'b'      => array(),
        'em'     => array(),
        'i'      => array(),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'a'      => array(
            'href'   => array(),
            'title'  => array(),
            'target' => array(),
        ),
    );

    /**
     * Get instance
     *
     * @return Soulmakers_Mail
     */
    public static function instance(): Soulmakers_Mail {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->register_fluentform_hooks();
    }

    /**
     * Register Fluent Forms hooks for custom email template
     */
    private function register_fluentform_hooks(): void {
        add_filter( 'fluentform/email_body', array( $this, 'wrap_fluentform_email' ), 10, 4 );
        add_filter( 'fluentform_email_styles', array( $this, 'reset_fluentform_email_styles' ), 10, 2 );
    }

    /**
     * Reset Fluent Forms email styles to remove default styling
     *
     * @param string $css  The default CSS styles.
     * @param object $form The form object.
     * @return string Modified CSS styles.
     */
    public function reset_fluentform_email_styles( string $css, $form ): string {
        return '
            /* Reset Fluent Forms default styles */
            .ff_email_body {
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .ff_email_container,
            .ff_email_wrapper {
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
            }
            table.ff_email_table {
                background: transparent !important;
                border: none !important;
                border-collapse: collapse !important;
                width: 100% !important;
            }
            table.ff_email_table td {
                background: transparent !important;
                border: none !important;
                padding: 4px 0 !important;
            }
            .ff_email_header,
            .ff_email_footer {
                display: none !important;
            }
            p, td, th, li {
                font-family: Inter, BlinkMacSystemFont, Segoe UI, Helvetica Neue, Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #1C1003;
            }
            h1, h2, h3, h4, h5, h6 {
                font-family: Inter, BlinkMacSystemFont, Segoe UI, Helvetica Neue, Arial, sans-serif;
                color: #1C1003;
                margin: 0 0 10px 0;
            }
            a {
                color: #962F10;
            }
        ';
    }

    /**
     * Wrap Fluent Forms email content in Soulmakers template
     *
     * @param string $emailBody     The email body HTML.
     * @param object $notification  The notification settings.
     * @param object $submittedData The form submission data.
     * @param object $form          The form object.
     * @return string Modified email body with Soulmakers template.
     */
    public function wrap_fluentform_email( $emailBody, $notification, $submittedData, $form ): string {
        $subject = isset( $notification->subject ) ? $notification->subject : 'Benachrichtigung';
        return $this->get_email_template( $subject, $emailBody );
    }

    /**
     * Send email
     *
     * @param string $to          Recipient email.
     * @param string $subject     Email subject.
     * @param string $message     Email message (HTML).
     * @param array  $attachments Optional attachments.
     * @param array  $headers     Optional headers.
     * @return bool
     */
    public function send( string $to, string $subject, string $message, array $attachments = array(), array $headers = array() ): bool {
        if ( empty( $headers ) ) {
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            $from_email = get_option( 'admin_email' );
            $from_name  = get_option( 'blogname' );
            $headers[]  = 'From: ' . $from_name . ' <' . $from_email . '>';
        }

        $html_message = $this->get_email_template( $subject, $message );

        $html_message = apply_filters( 'soulmakers_mail_message', $html_message, $to, $subject );
        $headers      = apply_filters( 'soulmakers_mail_headers', $headers, $to, $subject );
        $attachments  = apply_filters( 'soulmakers_mail_attachments', $attachments, $to, $subject );

        return wp_mail( $to, $subject, $html_message, $headers, $attachments );
    }

    /**
     * Get email template
     *
     * @param string $subject Email subject.
     * @param string $content Email content (HTML).
     * @return string Complete HTML email template.
     */
    public function get_email_template( string $subject, string $content ): string {
        $template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="de">
<head>
<title>' . esc_html( $subject ) . '</title>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!--[if !mso]>-->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<!--<![endif]-->
<meta name="x-apple-disable-message-reformatting" content="" />
<meta content="target-densitydpi=device-dpi" name="viewport" />
<meta content="true" name="HandheldFriendly" />
<meta content="width=device-width" name="viewport" />
<meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no" />
<style type="text/css">
table {
border-collapse: separate;
table-layout: fixed;
mso-table-lspace: 0pt;
mso-table-rspace: 0pt
}
table td {
border-collapse: collapse
}
.ExternalClass {
width: 100%
}
.ExternalClass,
.ExternalClass p,
.ExternalClass span,
.ExternalClass font,
.ExternalClass td,
.ExternalClass div {
line-height: 100%
}
body, a, li, p, h1, h2, h3 {
-ms-text-size-adjust: 100%;
-webkit-text-size-adjust: 100%;
}
html {
-webkit-text-size-adjust: none !important
}
body {
min-width: 100%;
Margin: 0px;
padding: 0px;
}
body, #innerTable {
-webkit-font-smoothing: antialiased;
-moz-osx-font-smoothing: grayscale
}
#innerTable img+div {
display: none;
display: none !important
}
img {
Margin: 0;
padding: 0;
-ms-interpolation-mode: bicubic
}
h1, h2, h3, p, a {
overflow-wrap: normal;
white-space: normal;
word-break: break-word
}
a {
text-decoration: none
}
h1, h2, h3, p {
min-width: 100%!important;
width: 100%!important;
max-width: 100%!important;
display: inline-block!important;
border: 0;
padding: 0;
margin: 0
}
a[x-apple-data-detectors] {
color: inherit !important;
text-decoration: none !important;
font-size: inherit !important;
font-family: inherit !important;
font-weight: inherit !important;
line-height: inherit !important
}
u + #body a {
color: inherit;
text-decoration: none;
font-size: inherit;
font-family: inherit;
font-weight: inherit;
line-height: inherit;
}
a[href^="mailto"],
a[href^="tel"],
a[href^="sms"] {
color: inherit;
text-decoration: none
}
</style>
<style type="text/css">
@media (min-width: 481px) {
.hd { display: none!important }
}
</style>
<style type="text/css">
@media (max-width: 480px) {
.hm { display: none!important }
}
</style>
<style type="text/css">
@media (max-width: 480px) {
.t44{text-align:left!important}.t35,.t43{vertical-align:top!important;width:600px!important}
}
</style>
<!--[if !mso]>-->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&amp;display=swap" rel="stylesheet" type="text/css" />
<!--<![endif]-->
<!--[if mso]>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
<![endif]-->
</head>
<body id="body" class="t54" style="min-width:100%;Margin:0px;padding:0px;background-color:#E4DCCF;"><div class="t53" style="background-color:#E4DCCF;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" align="center"><tr><td class="t52" style="font-size:0;line-height:0;mso-line-height-rule:exactly;background-color:#E4DCCF;" valign="top" align="center">
<!--[if mso]>
<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false">
<v:fill color="#E4DCCF"/>
</v:background>
<![endif]-->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" align="center" id="innerTable"><tr><td align="center">
<table class="t8" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="600" class="t7" style="width:600px;">
<table class="t6" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t5" style="overflow:hidden;padding:32px 32px 32px 32px;border-radius:0 0 8px 8px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100% !important;"><tr><td align="left">
<table class="t4" role="presentation" cellpadding="0" cellspacing="0" style="Margin-right:auto;"><tr><td width="200" class="t3" style="width:200px;">
<table class="t2" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t1"><div style="font-size:0px;"><img class="t0" style="display:block;border:0;height:auto;width:100%;Margin:0;max-width:100%;" width="200" height="auto" alt="Soulmakers" src="https://soulmakers.de/wp-content/uploads/soulmaker-logo-email.png"/></div></td></tr></table>
</td></tr></table>
</td></tr></table></td></tr></table>
</td></tr></table>
</td></tr><tr><td align="center">
<table class="t27" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="600" class="t26" style="width:600px;">
<table class="t25" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t24" style="overflow:hidden;border-radius:8px 8px 8px 8px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100% !important;"><tr><td align="center">
<table class="t23" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="600" class="t22" style="width:600px;">
<table class="t21" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t20" style="border:1px solid #C1B7A5;overflow:hidden;background-color:#FFFFFF;padding:32px 32px 32px 32px;border-radius:8px 8px 8px 8px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100% !important;"><tr><td align="center">
<table class="t13" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="534" class="t12" style="width:600px;">
<table class="t11" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t10"><div class="t9" style="font-family:Inter,BlinkMacSystemFont,Segoe UI,Helvetica Neue,Arial,sans-serif;line-height:28px;font-weight:400;font-style:normal;font-size:14px;text-decoration:none;text-transform:none;direction:ltr;color:#1C1003;text-align:left;mso-line-height-rule:exactly;mso-text-raise:4px;">' . $content . '</div></td></tr></table>
</td></tr></table>
</td></tr></table></td></tr></table>
</td></tr></table>
</td></tr></table></td></tr></table>
</td></tr></table>
</td></tr><tr><td align="center">
<table class="t51" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="600" class="t50" style="width:600px;">
<table class="t49" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t48"><div class="t47" style="width:100%;text-align:left;"><div class="t46" style="display:inline-block;"><table class="t45" role="presentation" cellpadding="0" cellspacing="0" align="left" valign="top">
<tr class="t44"><td></td><td class="t35" width="300" valign="top">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="t34" style="width:100%;"><tr><td class="t33"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100% !important;"><tr><td align="center">
<table class="t32" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="300" class="t31" style="width:600px;">
<table class="t30" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t29" style="padding:32px 32px 32px 32px;"><p class="t28" style="margin:0;Margin:0;font-family:Inter,BlinkMacSystemFont,Segoe UI,Helvetica Neue,Arial,sans-serif;line-height:22px;font-weight:500;font-style:normal;font-size:11px;text-decoration:none;text-transform:none;direction:ltr;color:#5A4632;text-align:left;mso-line-height-rule:exactly;mso-text-raise:3px;"><strong>Soulmakers GmbH</strong><br/><br/>Hardtstra&szlig;e 64<br/>40629 D&uuml;sseldorf<br/>Deutschland<br/><br/>Tel.: +49 173 3268525<br/>E-Mail: hallo@soulmakers.space<br/><br/>Registergericht: D&uuml;sseldorf<br/>Registernummer: HRB 103076<br/><br/>Gesch&auml;ftsf&uuml;hrer:<br/>Dominic Aquaro, Marc Andr&eacute; Dettmer</p></td></tr></table>
</td></tr></table>
</td></tr></table></td></tr></table>
</td><td class="t43" width="300" valign="top">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="t42" style="width:100%;"><tr><td class="t41"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100% !important;"><tr><td align="center">
<table class="t40" role="presentation" cellpadding="0" cellspacing="0" style="Margin-left:auto;Margin-right:auto;"><tr><td width="300" class="t39" style="width:600px;">
<table class="t38" role="presentation" cellpadding="0" cellspacing="0" width="100%" style="width:100%;"><tr><td class="t37" style="padding:32px 32px 32px 32px;"><p class="t36" style="margin:0;Margin:0;font-family:Inter,BlinkMacSystemFont,Segoe UI,Helvetica Neue,Arial,sans-serif;line-height:22px;font-weight:500;font-style:normal;font-size:11px;text-decoration:none;text-transform:none;direction:ltr;color:#5A4632;text-align:left;mso-line-height-rule:exactly;mso-text-raise:3px;">&copy; Soulmakers. Alle Rechte vorbehalten.<br/><br/><a href="https://soulmakers.de/impressum/" style="color:#962F10;text-decoration:underline;">Impressum</a><br/><a href="https://soulmakers.de/datenschutz/" style="color:#962F10;text-decoration:underline;">Datenschutz</a><br/><a href="https://soulmakers.de/agb/" style="color:#962F10;text-decoration:underline;">AGB</a></p></td></tr></table>
</td></tr></table>
</td></tr></table></td></tr></table>
</td>
<td></td></tr>
</table></div></div></td></tr></table>
</td></tr></table>
</td></tr></table></td></tr></table></div><div class="gmail-fix" style="display: none; white-space: nowrap; font: 15px courier; line-height: 0;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div></body>
</html>';

        return $template;
    }
}
