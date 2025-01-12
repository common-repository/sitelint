<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sitelint.com
 * @since      1.0.0
 *
 * @package    SiteLintPublic
 * @subpackage SiteLintPublic/public
 */
class SiteLintPublic
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/styles.css?c=' . filemtime(plugin_dir_path(__FILE__) . 'css/styles.css'),
            false,
            filemtime(plugin_dir_path(__FILE__) . 'css/styles.css')
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $sitelint = get_option('sitelint');

        $config = file_get_contents(__DIR__ . '/config.json');
        $appConfig = json_decode($config, true);

        if ($sitelint['active'] == true && !empty($sitelint['apiToken'])) {

            echo "<script id='auditor_script_admin'>(function(w,d,s,a,m,t) {

                const auditorScript = document.getElementById('auditor_script_public');
                const auditorAppScript = document.getElementById('auditor_app');

                if (auditorScript) {
                  auditorScript.remove();
                }

                if (auditorAppScript) {
                  auditorAppScript.remove();
                }

                a = d.createElement(s);m = d.getElementsByTagName(s)[0];a.defer = true;a.id = 'auditor_app';a.src = '" . esc_attr($appConfig['auditorUrl']) . "/auditor.bundle.js?tokenId=" .
                esc_html($sitelint['apiToken']) .
                "';

                function onPageLoaded() {
                  w.clearTimeout(t);
                  w.removeEventListener('DOMContentLoaded', onPageLoaded);
                  m.parentNode.insertBefore(a, m);
                }

                function onAuditorLoaded(){
                  auditor.config({
                    includeHidden: true,
                    stripTextFromReport: false
                  }).run();
                }

                function onBeforeUnload() {
                  const element = document.getElementById('auditor_app');
                  if (element) {
                    element.remove();
                  }
                }

                a.addEventListener('load', onAuditorLoaded);

                if (d.readyState !== 'loading') {
                  onPageLoaded();
                  return;
                }

                w.addEventListener('DOMContentLoaded', onPageLoaded);

                document.addEventListener('beforeunload', onBeforeUnload);
              })(window, document, 'script');</script>";
              return;
        }

        if ($sitelint['active'] == false || empty($sitelint['apiToken'])) {

            echo "<script id='auditor_script_public'>(function(w,d,s,a,m,t) {

                const auditorScript = document.getElementById('auditor_script_admin');
                const auditorAppScript = document.getElementById('auditor_app');

                if (auditorScript) {
                  auditorScript.remove();
                }

                if (auditorAppScript) {
                  auditorAppScript.remove();
                }

                a = d.createElement(s);m = d.getElementsByTagName(s)[0];a.defer = true;a.id = 'auditor_app';a.src = '" . esc_attr($appConfig['auditorUrl']) . "/auditor.bundle.js';

                function onPageLoaded() {
                  w.clearTimeout(t);
                  w.removeEventListener('DOMContentLoaded', onPageLoaded);
                  m.parentNode.insertBefore(a, m);
                }

                function onAuditorLoaded(){
                  auditor.config({
                    includeHidden: true,
                    stripTextFromReport: false,
                    skipSendingReports: true
                  }).run();
                }

                function onBeforeUnload() {
                  const element = document.getElementById('auditor_app');
                  if (element) {
                    element.remove();
                  }
                }

                a.addEventListener('load', onAuditorLoaded);

                if (d.readyState !== 'loading') {
                  onPageLoaded();
                  return;
                }

                w.addEventListener('DOMContentLoaded', onPageLoaded);

                document.addEventListener('beforeunload', onBeforeUnload);
              })(window, document, 'script');</script>";
        }

    }

    function add_query_params_if_plugin_active() {

      if(!is_user_logged_in()){
        return;
      }

      $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

      $parsed_url = wp_parse_url($current_url);
      $query_params = array();

      if (isset($parsed_url['query'])) {
          parse_str($parsed_url['query'], $query_params);
      }

      if (!array_key_exists('sitelint', $query_params)) {
          wp_redirect($new_url . "?sitelint=%5B%7B%22ui%22%3A%22full%22%7D%5D");
          die;
      }

}

    /**
     * Register the footer.
     *
     * @since    1.0.0
     */
    public function add_logo()
    {
        $sitelint = get_option('sitelint');

        if ($sitelint['addLogo'] == '1') {
            echo '<a href="https://www.sitelint.com/" rel="noopener" target="_blank" style="display: inline-block; height: 16px; left: 4px; line-height: initial; margin: -24px 0 0 0; position: absolute; padding: 0 0 0 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewbox="0 0 16 16"
                aria-hidden="true" focusable="false"><path fill="#0069c4" d="M0 0h16v16H0Z" />
                <path d="M4.316 10.489q3.41.187 4.617.187.287 0 .448-.162.174-.174.174-.46v-1.12H6.693q-1.306
                0-1.904-.586-.585-.597-.585-1.904v-.373q0-1.307.585-1.892.598-.597 1.904-.597h4.368v1.742h-3.87q-.747
                0-.747.747v.249q0 .746.747.746h2.24q1.22 0 1.792.573.572.572.572 1.792v.622q0 1.22-.572
                1.792-.573.572-1.792.572-.635 0-1.344-.024l-1.145-.05q-1.27-.062-2.626-.174z" fill="#fff" />
                </svg><span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
                overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;">SiteLint Audits: Monitoring in real-time Accessibility, Performance, Privacy, Security, SEO, Runtime Errors and Console Logs</span></a>';
        }
    }
}
