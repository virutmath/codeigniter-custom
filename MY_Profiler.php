<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class MY_Profiler extends CI_Profiler
{

    public function run()
    {
        $output = '';
        $output .=
            '<style>
    #codeigniter_wrap_profiler {
        position: fixed;
        bottom : 0;
        left: 0;
        z-index: 1000;
        width: 100%;
        height: 230px;
        overflow: hidden;
        padding-top: 4px;
        background: #fff;
        border-top: 1px solid #ccc;
        box-sizing : border-box;
    }
    #codeigniter_profiler_debug_bar {
        height: 26px;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        background: #f0f0f0;
    }
    #codeigniter_profiler_debug_bar_tab {
        float: left;
    }
    #codeigniter_profiler_logo {
        float: left;
        padding: 4px 9px 4px 4px;
        font-size: 12px;
        background: #666;
        color: #fff;
        cursor: default;
    }
    #codeigniter_profiler_logo img {
        width: 16px;
        height: 16px;
        margin-top: -4px;
    }
    #codeigniter_profiler_close {
        float: right;
        padding-right: 3px;
        background: #fff;
        padding-left: 3px;
        border-radius: 50%;
        line-height: 16px;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        margin-top: 3px;
        border: 1px solid #ccc;
        margin-left: 5px;
    }
    #codeigniter_profiler_x2 {
        float: right;
        padding-right: 3px;
        padding-left: 3px;
        cursor: pointer;
        line-height: 16px;
        text-align: center;
        font-size: 12px;
        border: 1px solid #ccc;
        border-radius: 50%;
        background: #fff;
        margin-top: 3px;
    }

    #codeigniter_profiler {
        overflow: auto;
        height: 200px;
    }
    #codeigniter_profiler fieldset legend {
        font-size: 14px;
        font-weight: bold;
    }
    #codeigniter_profiler fieldset table {
        font-size: 12px;
    }
</style>';
        $output .= '<div id="codeigniter_wrap_profiler">';
        $output .= $this->addDebugBar();
        $output .= parent::run();
        $output .= '</div>';
        //add script
        $output .=
            '<script>
var ci_profiler_wrap = document.getElementById("codeigniter_wrap_profiler");
var ci_profiler = document.getElementById("codeigniter_profiler");
var ci_profiler_bar = document.getElementById("codeigniter_profiler_debug_bar");
var ci_profiler_height = ci_profiler_wrap.offsetHeight;
var ci_profiler_fn = {
    defaultHeight: 230,
    triggerZoom: false,
    triggerClose: false,
    minimalBar : function() {
        ci_profiler_wrap.style.height = this.triggerClose ? ci_profiler_height + "px" : "31px";
        this.triggerClose = !this.triggerClose;
    },
    zoomBar: function() {
        if(this.triggerClose) {
            this.minimalBar();
            return;
        }
        ci_profiler_height = this.triggerZoom ? ci_profiler_wrap.offsetHeight / 2 : ci_profiler_wrap.offsetHeight * 2;
        ci_profiler_wrap.style.height = ci_profiler_height  + "px";
        ci_profiler.style.height = (ci_profiler_height - ci_profiler_bar.offsetHeight)  + "px";
        this.triggerZoom = !this.triggerZoom;
    }
};
document.body.style.marginBottom = ci_profiler_height + "px";
</script>';
        return $output;
    }
    protected function addDebugBar() {
        $debugBar = '';
        $debugBar .= '<div id="codeigniter_profiler_debug_bar">';
        $debugBar .= $this->debugBarTab();
        $debugBar .= '<div id="codeigniter_profiler_close" onclick="ci_profiler_fn.minimalBar()">
                            <span>&times;</span>
                      </div>';
        $debugBar .= '<div id="codeigniter_profiler_x2" onclick="ci_profiler_fn.zoomBar()">
                            <span>&#9744;</span>
                      </div>';
        $debugBar .= '</div>';
        return $debugBar;
    }
    protected function debugBarTab() {
        $tab = '<div id="codeigniter_profiler_debug_bar_tab">';
        $tab .= '<span id="codeigniter_profiler_logo"><img src="https://codeigniter.com/assets/images/ci-logo-white.png"/> CI Profiler</span>';
        $tab .= '</div>';
        return $tab;
    }
}
