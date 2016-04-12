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
        this.resizeBody();
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
        this.resizeBody();
    },
    resizeBody: function() {
        document.body.style.marginBottom = ci_profiler_wrap.offsetHeight + "px";
    }
};
ci_profiler_fn.resizeBody();
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
        $tab .= '<span id="codeigniter_profiler_logo"><img src="data:image/png;base64,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
"/> CI Profiler</span>';
        $tab .= '</div>';
        return $tab;
    }
}
