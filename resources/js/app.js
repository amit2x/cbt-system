// import './bootstrap';
import * as bootstrap from 'bootstrap'
window.bootstrap = bootstrap
console.log('Bootstrap loaded');

/* =============================
   jQuery
============================= */

import $ from "jquery";

window.$ = window.jQuery = $;

console.log("jQuery loaded");


/* =============================
   Select2
============================= */

import select2 from "select2";

select2($);   // attach plugin to jQuery

import 'select2/dist/css/select2.min.css';

console.log("Select2 loaded");

import Sortable from 'sortablejs';
window.Sortable = Sortable;

console.log("SortableJS loaded");

/* =============================
   Chart.js
============================= */

import Chart from 'chart.js/auto';
window.Chart = Chart;

console.log("Chart.js loaded");


/* =============================
   Toastr
============================= */

import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

window.toastr = toastr;

console.log("Toastr loaded");

/* =============================
   Toastr Config
============================= */

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: "4000"
};

console.log("Toastr configured");


/* =============================
   DOM Ready
============================= */

$(function () {

    console.log("DOM readies");

    if ($('.select2').length) {

        console.log('test');

        $('.select2').select2({
            width: '100%'
        });

        console.log("Select2 initialized");

    }


    if (window.flashMessages) {

        const messages = window.flashMessages;

        if (messages.success) toastr.success(messages.success);
        if (messages.error) toastr.error(messages.error);
        if (messages.warning) toastr.warning(messages.warning);
        if (messages.info) toastr.info(messages.info);

    }

});
