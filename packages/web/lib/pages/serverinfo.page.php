<?php
/**
 * Presents server information when clicked.
 *
 * PHP version 5
 *
 * @category ServerInfo
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Presents server information when clicked.
 *
 * @category ServerInfo
 * @package  FOGProject
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class ServerInfo extends FOGPage
{
    /**
     * The node this works off of.
     *
     * @var string
     */
    public $node = 'hwinfo';
    /**
     * Initializes the server information.
     *
     * @param string $name The name this initializes with.
     *
     * @return void
     */
    public function __construct($name = '')
    {
        $this->name = 'Hardware Information';
        parent::__construct($this->name);
        global $id;
        $this->obj = new StorageNode($id);
    }
    /**
     * The index page.
     *
     * @return void
     */
    public function index(...$args)
    {
        $this->title = _('Server Information');
        if (!$this->obj->isValid()) {
            echo '<div class="col-md-12">';
            echo '<div class="box box-warning">';
            echo '<div class="box-header with-border">';
            echo '<h4 class="box-title">';
            echo $this->title;
            echo '</h4>';
            echo '<div class="box-tools pull-right">';
            echo self::$FOGCollapseBox;
            echo self::$FOGCloseBox;
            echo '</div>';
            echo '</div>';
            echo '<div class="box-body">';
            echo _('Invalid Server Information!');
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }
        $url = sprintf(
            '%s://%s/fog/status/hw.php',
            self::$httpproto,
            $this->obj->get('ip')
        );
        if (!$this->obj->get('online')) {
            echo '<div class="col-md-12">';
            echo '<div class="box box-warning">';
            echo '<div class="box-header with-border">';
            echo '<h4 class="box-title">';
            echo $this->title;
            echo '</h4>';
            echo '<div class="box-tools pull-right">';
            echo self::$FOGCollapseBox;
            echo self::$FOGCloseBox;
            echo '</div>';
            echo '</div>';
            echo '<div class="box-body">';
            echo _('Server appears to be offline or unavailable!');
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }
        $ret = self::$FOGURLRequests->process($url);
        if (!$ret) {
            echo '<div class="col-md-12">';
            echo '<div class="box box-warning">';
            echo '<div class="box-header with-border">';
            echo '<h4 class="box-title">';
            echo $this->title;
            echo '</h4>';
            echo '<div class="box-tools pull-right">';
            echo self::$FOGCollapseBox;
            echo self::$FOGCloseBox;
            echo '</div>';
            echo '</div>';
            echo '<div class="box-body">';
            echo _('Server appears to be offline or unavailable!');
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }
        $ret = trim($ret[0]);
        $section = 0;
        $arGeneral = [];
        $arFS = [];
        $arNIC = [];
        $lines = explode("\n", $ret);
        foreach ((array)$lines as &$line) {
            $line = trim($line);
            switch ($line) {
            case '@@start':
            case '@@end':
                break;
            case '@@general':
                $section = 0;
                break;
            case '@@fs':
                $section = 1;
                break;
            case '@@nic':
                $section = 2;
                break;
            default:
                switch ($section) {
                case 0:
                    $arGeneral[] = $line;
                    break;
                case 1:
                    $arFS[] = $line;
                    break;
                case 2:
                    $arNIC[] = $line;
                }
            }
            unset($line);
        }
        if (count($arGeneral) < 1) {
            echo '<div class="col-md-12">';
            echo '<div class="box box-warning">';
            echo '<div class="box-header with-border">';
            echo '<h4 class="box-title">';
            echo _('General Information');
            echo '</h4>';
            echo '<div class="box-tools pull-right">';
            echo self::$FOGCollapseBox;
            echo self::$FOGCloseBox;
            echo '</div>';
            echo '</div>';
            echo '<div class="box-body">';
            echo _('Unable to find basic information!');
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }
        foreach ((array)$arNIC as &$nic) {
            $nicparts = explode("$$", $nic);
            if (count($nicparts) == 5) {
                $NICTransSized[] = self::formatByteSize($nicparts[2]);
                $NICRecSized[] = self::formatByteSize($nicparts[1]);
                $NICErrInfo[] = $nicparts[3];
                $NICDropInfo[] = $nicparts[4];
                $NICTrans[] = sprintf('%s %s', $nicparts[0], _('TX'));
                $NICRec[] = sprintf('%s %s', $nicparts[0], _('RX'));
                $NICErr[] =    sprintf('%s %s', $nicparts[0], _('Errors'));
                $NICDro[] = sprintf('%s %s', $nicparts[0], _('Dropped'));
            }
            unset($nic);
        }
        $fields = [
            _('Storage Node') => $this->obj->get('name'),
            _('IP') => self::resolveHostname(
                $this->obj->get('ip')
            ),
            _('Kernel') => $arGeneral[0],
            _('Hostname') => $arGeneral[1],
            _('Uptime') => $arGeneral[2],
            _('CPU Type') => $arGeneral[3],
            _('CPU Count') => $arGeneral[4],
            _('CPU Model') => $arGeneral[5],
            _('CPU Speed') => $arGeneral[6],
            _('CPU Cache') => $arGeneral[7],
            _('Total Memory') => $arGeneral[8],
            _('Used Memory') => $arGeneral[9],
            _('Free Memory') => $arGeneral[10]
        ];
        $fogversion = $arGeneral[11];
        // Running FOG Version
        echo '<div class="box box-primary">';
        echo '<div class="box-header with-border">';
        echo '<h4 class="box-title">';
        echo _('FOG Version');
        echo '</h4>';
        echo '<div class="box-tools pull-right">';
        echo self::$FOGCollapseBox;
        echo self::$FOGCloseBox;
        echo '</div>';
        echo '</div>';
        echo '<div class="box-body">';
        echo $fogversion;
        echo '</div>';
        echo '</div>';
        unset($fogversion);
        // General Info
        ob_start();
        foreach ($fields as $field => &$input) {
            echo '<div class="col-md-4 pull-left">';
            echo $field;
            echo '</div>';
            echo '<div class="col-md-8 pull-right">';
            echo $input;
            echo '</div>';
            unset($field, $input);
        }
        $rendered = ob_get_clean();
        echo '<div class="box box-primary">';
        echo '<div class="box-header with-border">';
        echo '<h4 class="box-title">';
        echo _('General Information');
        echo '</h4>';
        echo '<div class="box-tools pull-right">';
        echo self::$FOGCollapseBox;
        echo self::$FOGCloseBox;
        echo '</div>';
        echo '</div>';
        echo '<div class="box-body">';
        echo $rendered;
        echo '</div>';
        echo '</div>';
        unset(
            $fields,
            $rendered
        );
        // File System Info
        $fields = [
            _('Total Disk Space') => $arFS[0],
            _('Used Disk Space') => $arFS[1],
            _('Free Disk Space') => $arFS[2]
        ];
        ob_start();
        foreach ($fields as $field => &$input) {
            echo '<div class="col-md-4 pull-left">';
            echo $field;
            echo '</div>';
            echo '<div class="col-md-8 pull-right">';
            echo $input;
            echo '</div>';
            unset($field, $input);
        }
        $rendered = ob_get_clean();
        echo '<div class="box box-primary">';
        echo '<div class="box-header with-border">';
        echo '<h4 class="box-title">';
        echo _('File System Information');
        echo '</h4>';
        echo '<div class="box-tools pull-right">';
        echo self::$FOGCollapseBox;
        echo self::$FOGCloseBox;
        echo '</div>';
        echo '</div>';
        echo '<div class="box-body">';
        echo $rendered;
        echo '</div>';
        echo '</div>';
        unset(
            $fields,
            $rendered,
            $this->data
        );
        // Network Information.
        echo '<div class="box box-primary">';
        echo '<div class="box-header with-border">';
        echo '<h4 class="box-title">';
        echo _('Network Information');
        echo '</h4>';
        echo '<div class="box-tools pull-right">';
        echo self::$FOGCollapseBox;
        echo self::$FOGCloseBox;
        echo '</div>';
        echo '</div>';
        echo '<div class="box-body">';
        echo '<div class="box-group" id="accordion">';
        foreach ((array)$NICTrans as $index => &$txtran) {
            unset(
                $fields,
                $this->data
            );
            $ethName = explode(' ', $txtran);
            $fields = [
                $NICTrans[$index] => $NICTransSized[$index],
                $NICRec[$index] => $NICRecSized[$index],
                $NICErr[$index] => $NICErrInfo[$index],
                $NICDro[$index] => $NICDropInfo[$index]
            ];
            ob_start();
            foreach ($fields as $field => &$input) {
                echo '<div class="col-md-3 pull-left">';
                echo $field;
                echo '</div>';
                echo '<div class="col-md-9 pull-right">';
                echo $input;
                echo '</div>';
                unset($field, $input);
            }
            $rendered = ob_get_clean();
            echo '<div class="panel box box-primary">';
            echo '<div class="box-header with-border">';
            echo '<h4 class="box-title">';
            echo '<a data-toggle="collapse" data-parent="#accordion" href="#'
                . $ethName[0]
                . '">';
            echo $ethName[0];
            echo ' ';
            echo _('Information');
            echo '</a>';
            echo '</h4>';
            echo '</div>';
            echo '<div id="'
                . $ethName[0]
                . '" class="panel-collapse collapse">';
            echo '<div class="box-body">';
            echo $rendered;
            echo '</div>';
            echo '</div>';
            echo '</div>';
            unset($txtran, $rendered);
        }
        echo '</div>';
        echo '</div>';
        unset(
            $arGeneral,
            $arNIC,
            $arFS,
            $NICTransSized,
            $NICRecSized,
            $NICErrInfo,
            $NICDropInfo,
            $NICTrans,
            $NICRec,
            $NICErr,
            $NICDro,
            $fields
        );
        echo '</div>';
    }
}