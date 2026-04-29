const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix
	.webpackConfig({ devtool: "source-map" })
	.copyDirectory('resources/fonts/montserrat', 							'public/fonts/vendor/@montserrat')
    .copy('resources/fonts/montserrat.css', 								'public/css/vendor/fonts/montserrat.css')
	.copy('node_modules/gridstack/dist/gridstack.min.css', 					'public/css/vendor/widgets/gridstack.min.css')
	.copy('node_modules/gridstack/dist/gridstack-extra.min.css',			'public/css/vendor/widgets/gridstack-extra.min.css')
	.sass('resources/sass/widgets.scss',									'public/css/vendor/widgets/widgets.css'							).sourceMaps(true, 'source-map').version()
	.styles(
	[
		'public/css/vendor/widgets/gridstack.min.css',
		'public/css/vendor/widgets/gridstack-extra.min.css',
		'public/css/vendor/widgets/widgets.css'
	],																		'public/css/widgets.css')

	.sass('resources/sass/filemanager.scss',								'public/css/vendor/filemanager/filemanager.css'					).sourceMaps(true, 'source-map').version()
	.copy('node_modules/dropzone/dist/min/dropzone.min.css',				'public/css/vendor/filemanager/dropzone.min.css')
	.copy('resources/sass/vendor/material-floating-button/mfb.css',			'public/css/vendor/filemanager/mfb.css')
	.styles(
	[
		'public/css/vendor/filemanager/filemanager.css',
		'public/css/vendor/filemanager/dropzone.min.css',
		'public/css/vendor/filemanager/mfb.css'
	],																		'public/css/filemanager.css')

    .sass('resources/sass/fonts.scss',                                      'public/css'                                            ).sourceMaps(true, 'source-map').version()
    .sass('node_modules/bootstrap/scss/bootstrap.scss',                     'public/css/vendor/bootstrap/bootstrap.css'             ).sourceMaps()
    .sass('resources/sass/fontawesome.scss',                                'public/css/vendor/fonts/fontawesome-all.css'           ).sourceMaps()
    .sass('node_modules/@mdi/font/scss/materialdesignicons.scss',           'public/css/vendor/fonts/material-design-iconic-font'   ).sourceMaps()
    .sass('resources/sass/datatables.scss',                                 'public/css/vendor/datatables'                          ).sourceMaps(true, 'source-map').version()
    .sass('resources/sass/vendor/palette-color-picker/palette-color-picker.scss',
																			'public/css/vendor/palette-color-picker'                ).sourceMaps()
    .sass('resources/sass/vendor/treant-js/treant.scss',					'public/css/vendor/treant-js/Treant.css'                ).sourceMaps()

    .sass('resources/sass/style.scss',                                      'public/css'                                            ).sourceMaps(true, 'source-map').version()

	.scripts(
    [
        'node_modules/datatables.net/js/jquery.dataTables.min.js',
        'node_modules/datatables.net-bs4/js/dataTables.bootstrap4.min.js',
        'node_modules/datatables.net-buttons/js/dataTables.buttons.min.js',
        'node_modules/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js',
        'node_modules/datatables.net-buttons/js/buttons.colVis.min.js',
        'node_modules/datatables.net-rowreorder/js/dataTables.rowReorder.min.js',
        'node_modules/datatables.net-rowreorder-bs4/js/rowReorder.bootstrap4.min.js',
        'resources/js/datatable.js'
    ],                                                                      'public/js/datatables.js')

	.copyDirectory('node_modules/tinymce', 									'public/js/tinymce')

	.copyDirectory('resources/js/langs', 									'public/js/tinymce/langs')

	.scripts(
        [
			'node_modules/tinymce/tinymce.min.js',
			'resources/js/tinymce-ext.js',
        ],															        'public/js/tinymce/tinymce-dintra.js'                  	).sourceMaps()

	.scripts(
        [
			'resources/js/date-range-locale.js',
			'node_modules/daterangepicker/daterangepicker.js',
			'resources/js/story-date-range-picker.js',
        ],															        'public/js/story-date-range-picker.js'                  	).sourceMaps()

	.scripts(
        [
            'node_modules/chart.js/dist/Chart.min.js',
			'node_modules/chartist/dist/chartist.js',
			'node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js',
			'node_modules/c3/c3.js',
			'node_modules/d3/dist/d3.js',
			'node_modules/daterangepicker/daterangepicker.js',
			'node_modules/daterangepicker/moment.min.js',
            'resources/js/dashboard.js'
        ],															        'public/js/dashboard.js'                               	).sourceMaps()
    .sass('resources/sass/dashboard.scss',                                  'public/css'                                            ).sourceMaps(true, 'source-map').version()

	.scripts([
		'node_modules/jquery/dist/jquery.min.js',
		'node_modules/jquery-slimscroll/jquery.slimscroll.js',
		'node_modules/popper.js/dist/umd/popper.min.js',
		'node_modules/bootstrap/dist/js/bootstrap.min.js',
		'resources/js/vendor/chosen/chosen.jquery.js',
		'node_modules/moment/min/moment.min.js',
		'node_modules/moment-range/dist/moment-range.js',
		'node_modules/moment/locale/hu.js',
		'resources/js/moment-ext.js',
        'node_modules/inputmask/dist/min/jquery.inputmask.bundle.min.js',
        'node_modules/inputmask.phone/dist/min/inputmask.phone/inputmask.phone.extensions.min.js',
        'node_modules/inputmask.phone/dist/min/inputmask.phone/phone-codes/phone-hu.min.js',
        'node_modules/flatpickr/dist/flatpickr.min.js',
        'node_modules/flatpickr/dist/l10n/hu.js',
        'resources/js/vendor/palette-color-picker/palette-color-picker.min.js',
		'resources/js/vendor/treant-js/vendor/raphael.js',
		'resources/js/vendor/treant-js/Treant.js',
		'resources/js/vendor/drag-to-scroll/dist/jquery.dragnscroll.js',
		'resources/js/vendor/zoom-range-slider/src/content-zoom-slider.min.js',
		'node_modules/html2canvas/dist/html2canvas.min.js'
	], 																		'public/js/vendor.js')

	.scripts([
		'node_modules/@fullcalendar/core/main.min.js',
		'node_modules/@fullcalendar/list/main.min.js',
		'node_modules/@fullcalendar/daygrid/main.min.js',
		'node_modules/@fullcalendar/timegrid/main.min.js',
		'node_modules/@fullcalendar/core/locales/hu.js'
	], 																		'public/js/fullcalendar.js')

	.scripts([
		'resources/js/date-range-locale.js',
		'node_modules/daterangepicker/daterangepicker.js',
	], 																		'public/js/daterangepicker.js')

	.scripts([
		'public/vendor/laravel-filemanager/js/stand-alone-button.js',
		'resources/js/lfm-standalone-ext.js',
	], 																		'public/js/stand-alone-button.js')

	.scripts([
		'resources/js/item-table-modal.js',
		'resources/js/flatpickr.js',
		'resources/js/employee.js'
	],																		'public/js/item-table-modal.js')

	.scripts([
		'resources/js/task-category.js'
	], 'public/js/task-category.js')

	.scripts([
		'resources/js/configuration-page.js'
	], 'public/js/configuration-page.js')

    .scripts(
	[
        'resources/js/app.js',
        'resources/js/pageelements.js',
        'resources/js/formelements.js',
        'resources/js/flatpickr.js',
		'resources/js/import.js',
		'resources/js/modal.js',
		'resources/js/taskman.js'
	], 																		'public/js/app.js' 										).sourceMaps()

	.copy('node_modules/chosen-js/chosen-sprite.png',						'public/css/vendor/chosen/chosen-sprite.png')
	.copy('node_modules/chosen-js/chosen-sprite@2x.png',					'public/css/vendor/chosen/chosen-sprite@2x.png')

	.scripts(
	[
		'node_modules/gridstack/dist/gridstack-h5.js',
		'node_modules/jquery-ui-dist/jquery-ui.min.js',
		'node_modules/bootbox/dist/bootbox.min.js',
		'resources/js/widgets.js',
		'resources/js/open-ai-chat.js'
	],																		'public/js/widgets.js')

	.scripts(
	[
		'node_modules/dropzone/dist/min/dropzone.min.js',
		'resources/js/vendor/jquery-form/jquery.form.min.js',
		'resources/js/filemanager/mfb.js',
		'resources/js/filemanager/filemanager.js'
	],																		'public/js/filemanager.js')

	.scripts(
	[
		'resources/js/documents.js'
	],																		'public/js/documents.js')

	.scripts(
	[
		'resources/js/withdraw.js'
	],																		'public/js/withdraw.js')

	.scripts(
	[
		'resources/js/create-document.js'
	],																		'public/js/create-document.js')

	.scripts(
	[
		'resources/js/preview-header.js'
	],																		'public/js/preview-header.js')

	.scripts(
	[
		'resources/js/reservations.js'
	],																		'public/js/reservations.js')

	.scripts(
	[
		'resources/js/resources.js'
	],																		'public/js/resources.js')

	.scripts(
	[
		'resources/js/leave-request.js'
	],																		'public/js/leave-request.js')

	.scripts(
	[
		'resources/js/customercrm.js'
	],																		'public/js/customercrm.js')

	.scripts(
	[
		'node_modules/jquery-ui-dist/jquery-ui.min.js',
		'resources/js/task.js'
	],																		'public/js/task.js')
	.scripts(
	[
		'node_modules/marked/marked.min.js',
		'resources/js/open-ai.js'
	],																		'public/js/open-ai.js')
	.scripts(
	[
		'node_modules/marked/marked.min.js',
		'resources/js/job-scope.js'
	],																		'public/js/job-scope.js')

	.scripts(
	[
		'resources/js/keyword-input.js'
	],																		'public/js/keyword-input.js')

	.scripts(
	[
		'resources/js/keyword-input-work.js'
	],																		'public/js/keyword-input-work.js')

	.scripts(['resources/js/sync.js',], 'public/js/sync.js' ).sourceMaps()

	.scripts(
	[
		'node_modules/bootbox/dist/bootbox.min.js'
	],																		'public/js/bootbox.js')

	.scripts(
	[
		'node_modules/dropzone/dist/dropzone.js'
	],																		'public/js/dropzone.js')

	.scripts([
		'node_modules/croppie/croppie.js',
	], 																		'public/js/croppie.js')

	.styles([
		'node_modules/dropzone/dist/dropzone.css'
	], 																		'public/css/vendor/dropzone/dropzone.css')

	.styles([
		'node_modules/croppie/croppie.css'
	], 																		'public/css/vendor/croppie/croppie.css')

	.styles([
		'node_modules/@fullcalendar/core/main.min.css',
		'node_modules/@fullcalendar/list/main.min.css',
		'node_modules/@fullcalendar/daygrid/main.min.css',
		'node_modules/@fullcalendar/timegrid/main.min.css'
	], 																		'public/css/vendor/fullcalendar/fullcalendar.min.css')

	.styles('node_modules/daterangepicker/daterangepicker.css', 			'public/css/vendor/daterangepicker/daterangepicker.min.css')
	.sass('resources/sass/statechanges.scss',                               'public/css'                                            ).sourceMaps(true, 'source-map').version()
	.scripts(
	[
		'resources/js/jquery.doubleScroll.js',
		'resources/js/statechanges.js',
	],																		'public/js/statechanges.js')

	.scripts(
	[
		'node_modules/jquery-ui-dist/jquery-ui.min.js',
		'resources/js/task.js'
	],																		'public/js/task.js')

	.scripts(
	[
		'resources/js/customer.js'
	],																		'public/js/customer.js')

	.scripts(
	[
		'resources/js/own-company.js'
	],																		'public/js/own-company.js')

	.scripts(
	[
		'resources/js/vendor/jquery-ui-touch-punch/jquery.ui.touch-punch.js'
	],																		'public/js/jquery.ui.touch-punch.js')

	.scripts(
	[
		'node_modules/chart.js/dist/Chart.min.js',
		'node_modules/chartist/dist/chartist.js',
		'node_modules/c3/c3.js',
		'node_modules/d3/dist/d3.js',
		'resources/js/own-data.js',
	],																		'public/js/own-data.js')

	.sass('resources/sass/custom_reservation.scss',                         'public/css' ).sourceMaps(true, 'source-map').version()
	.sass('resources/sass/task.scss',                         				'public/css' ).sourceMaps(true, 'source-map').version()
	.sass('resources/sass/task-category.scss',                         				'public/css' ).sourceMaps(true, 'source-map').version()
    .sass('resources/sass/customer.scss',                      				'public/css' ).sourceMaps(true, 'source-map').version()
    .sass('resources/sass/workstate_changes_state_change_modal.scss',                      				'public/css' ).sourceMaps(true, 'source-map').version()
