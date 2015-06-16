<?php
$plugin_info       = array(
    'pi_name'        => 'Utility Functions',
    'pi_version'     => '1.0.0',
    'pi_author'      => 'Linus Design',
    'pi_author_url'  => 'http://linusdesign.co.uk',
    'pi_description' => 'A selection of utility functions for use in templates.',
    'pi_usage'       => ld_util::usage()
);

class ld_util {

    public function htmlspecialchars() {
        $this->EE = &get_instance();
        return htmlspecialchars($this->EE->TMPL->tagdata);
    }
    public function escapequotes() {
        $this->EE = &get_instance();
        return str_replace('"', '&quot;', $this->EE->TMPL->tagdata);
    }

    public static function usage() {
        return '{exp:ld_util:htmlspecialchars}Place unescaped content here...{/exp:ld_util:htmlspecialchars}';
    }

}
/* End of file pi.util.php */
/* Location: ./system/third_party/util/pi.ld_util.php */