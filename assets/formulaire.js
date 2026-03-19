
jQuery(function() {
	jQuery('.select2').each(function() {
		var $select = jQuery(this);
		var maxSelectionLength = $select.data('maximum-selection-length');

		$select.select2({
			theme: 'bootstrap-5',
			width: '100%',
			minimumResultsForSearch: 10,
			language: {
				noResults: () => "Aucun résultat trouvé",
				maximumSelected: (args) => {
					var max = args && args.maximum ? Number(args.maximum) : 0;
					return max > 1
						? "Vous pouvez sélectionner jusqu'à " + max + " elements"
						: "Vous pouvez sélectionner " + max + " element";
				}
			},
			placeholder: $select.data('placeholder'),
			maximumSelectionLength: Number(maxSelectionLength) || 0,
		}).on('select2:selecting', function () {
			setTimeout(function () {
				$select.select2('open');
			}, 0);
		});
	});
});

jQuery(function(){

	initForm(jQuery('form.form')) ;

	jQuery('.mc-rows select[name$="[type]"]').each(function(){
		selectChange(jQuery(this),true) ;
	}) ;

	checkTarifs() ;

}) ;

jQuery(document).on('click','form.form .btn-submit',function(){
	jQuery(this).closest('form.form').trigger('submit') ;
}) ;

/**
 * à l'enregistrement on va parcourir tous les champs pour les vérifier.
 * 
 */
jQuery(document).on('submit','form.form',function(e){

	var ko = [];

	jQuery(this).find('select, input, textarea').each(function(){
		var row = jQuery(this).closest('tr, .mc-row, .contact-row, .date-row, .tarif-row, .illustration-row, .multimedia-row');
		var typeSelect = row.find('select').val();
		var okChamp = valideChamp(jQuery(this), typeSelect);

		if (jQuery(this).attr('id') == 'portee')
			console.log('doTest', jQuery(this), typeSelect, okChamp);

		jQuery(this).closest('.form-group, div').toggleClass('has-error',!okChamp) ;
		if ( ! okChamp )
		{
			ko.push(jQuery(this));
		}
	});
	
	var erreurMC = checkMC() ;
	if ( erreurMC !== true )
	{
		ko.push('erreurMC');
	}

	var erreurTarif = checkTypeTarifs() ;
	if ( erreurTarif !== true )
	{
		ko.push('erreurTarif');
	}

	var erreurContacts = checkContacts() ;
	if ( erreurContacts !== true )
	{
		ko.push('erreurContacts');
	}

	var erreurIllustrations = checkFilesInput('illustrations') ;
	if ( erreurIllustrations !== true )
	{
		ko.push('erreurIllustrations');
	}

	var erreurMultimedias = checkFilesInput('multimedias') ;
	if ( erreurMultimedias !== true )
	{
		ko.push('erreurMultimedias');
	}

	if ( ko.length == 0 )
	{
		jQuery(this).css('opacity',0.5) ;
		jQuery('input.btn-submit').closest('div').replaceWith('<div class="alert alert-warning loading">Formulaire en cours d\'enregistrement, veuillez patienter...</div>');
		return true ;
	}
	else
	{
		console.log(ko);
		$([document.documentElement, document.body]).animate({
			scrollTop: jQuery('.has-error').first().offset().top - 50
		}, 100);
		e.preventDefault() ;
		e.stopImmediatePropagation();
		alert('Votre formulaire comporte des erreurs : merci de vérifier les champs encadrés en rouge') ;
		return false ;
	}

}) ;

// Clone une ligne (sections en div.row avec data-rows-container / data-row-selector)
jQuery(document).on('click', '[data-rows-container][data-row-selector] .btn', function () {
	var plus = jQuery(this).closest('[data-rows-container][data-row-selector]') ;
	var container = plus.closest('fieldset, form').find(plus.data('rows-container')).first() ;
	var rowSelector = plus.data('row-selector') ;
	if ( ! container.length || ! rowSelector ) return ;

	var ligne = container.find(rowSelector).first().clone() ;
	container.append(ligne) ;

	var champs = ligne.find('input, select, textarea') ;
	champs.each(function(){
		jQuery(this).removeAttr('required') ;
		jQuery(this).val('') ;
		if ( container.hasClass('mc-rows') ) jQuery(this).attr('placeholder','') ;
		jQuery(this).removeClass('hasDatepicker hasTimepicker').attr('id',null) ;
	}) ;
	ligne.find('.description').each(function() {
		jQuery(this).html('') ;
	}) ;
	ligne.find('.moins').html(icon_moins) ;

	setIndent(container, rowSelector) ;
	initForm(container) ;
	if ( plus.hasClass('tarifs-plus') ) valideTarifUnique() ;
}) ;

jQuery(document).on('click', '.moins .btn', function () {
	var row = jQuery(this).closest('.mc-row, .contact-row, .date-row, .tarif-row, .illustration-row, .multimedia-row') ;
	var container = row.parent() ;
	var rowSelector = container.data('row-selector') ;
	row.remove() ;
	if ( rowSelector ) {
		setIndent(container, rowSelector) ;
		initForm(container) ;
	}
	if ( container.hasClass('tarifs-rows') ) valideTarifUnique() ;
}) ;


jQuery(document).on('click','div.date span.input-group-addon',function(){
	jQuery(this).closest('div').find('button').trigger('click') ;
}) ;

jQuery(document).on('click','div.time span.input-group-addon',function(){
	jQuery(this).closest('div').find('input').focus() ;
}) ;










jQuery(document).on('change','.mc-rows select[name$="[type]"]',function(){
	selectChange(jQuery(this)) ;
}) ;

jQuery(document).on('change','input[type="url"]',function(){
	selectChange(jQuery(this).closest('tr, .mc-row').find('select[name$="[type]"]'), true) ;
}) ;

jQuery(document).on('change','form.form input[name="gratuit"]',function(){
	checkTarifs();
}) ;

jQuery(document).on('change focusout','form.form select, form.form input, form.form textarea',function(){
	jQuery(this).closest('.form-group, div').toggleClass('has-error',!valideChamp(jQuery(this))) ;
}) ;

jQuery(document).on('change','.tarifs-rows select[name^="tarifs"]',function(){
	valideTarifUnique() ;
}) ;

function valideChamp(champ)
{
	var val = champ.val();
	
	if (champ.is(':checkbox') && ! champ.is(':checked')) {
		val = '';
	}

	if (val === null) val = '';

	var type = null ;
	if ( typeof champ.attr('name') !== 'undefined' && champ.attr('name').match(/\[coordonnee\]$/) )
		type = champ.closest('tr, .mc-row').find('select').val() ;

	if ( champ.attr('id') == 'portee' )
		console.log('valideChamp', type, val, champ.prop('required'));

	if ( val == '' && ! champ.prop('required') ) return true ;
	if ( val == '' && champ.prop('required') ) return false ;

	if ( champ.hasClass('date') )
	{
		var reg = /date\[([0-9]+)\]\[(debut|fin)\]/i ;
		var match = champ.attr('name').match(reg) ;

		if ( match.length != 3 ) return false ;

		var i = match[1] ;
		var t = match[2] ; // debut|fin

		if ( t == 'debut' )
		{
			var fin = champ.closest('.form').find('input[name="date\['+i+'\]\[fin\]"]') ;
			if ( fin.val() == '' ) fin.val(val) ;
			else if ( fin.val() < champ.val() ) fin.val(champ.val()) ;
		}
		else if ( t == 'fin' )
		{
			var debut = champ.closest('.form').find('input[name="date\['+i+'\]\[debut\]"]') ;
			if ( debut.val() == '' ) debut.val(val) ;
			else if ( champ.val() < debut.val() ) debut.val(champ.val()) ;
		}
	}
	else if ( champ.hasClass('float') )
	{
		champ.val(champ.val().replace(/[;\.,\-]/g,'.')) ;
		champ.val(champ.val().replace(/[^0-9\.]/g,'')) ;
		if ( ! champ.val().match(/^-?\d*([\.]{1}\d+)?$/) ) return false ;
	}
	else if ( type == 201 || champ.hasClass('telephone') ) // Téléphone
	{
		var devise = jQuery('form.form').find('input[name="devise"]').val() ;
		if ( devise == 'EUR' )
		{
			champ.val(val.replace(/[^0-9]/g,'')) ;
			if ( ! champ.val().match(/^[0-9]{10}$/) ) return false ;
			var beautify = champ.val().match(/([0-9+]{1,2})/g) ;
			if ( typeof beautify == 'object' && beautify != null ) champ.val(beautify.join(' ')) ;
		}
		else if ( devise == 'CHF' || devise == 'XPF' )
		{
			var tmp = val.replace(/[^0-9+]/g,'') ;
			if ( ! tmp.match(/^[0-9+]{6,14}$/) ) return false ;
		}
	}
	else if ( type == 204 || champ.hasClass('mail') ) // Mél
	{
		// https://stackoverflow.com/questions/46155/how-to-validate-email-address-in-javascript
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/ ;
		if ( ! re.test(val) ) return false ;
	}
	else if ( type == 205 || champ.hasClass('url') ) // Site web
	{
		var re = /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/ ;
		if ( ! re.test(val) ) return false ;
	}
	return true ;
}



function selectChange(select,init)
{
	var row = select.closest('tr, .mc-row') ;
	var coord = row.find('input[name$="[coordonnee]"]') ;
	row.find('small.h205').hide() ;

	if ( select.val() == 201 ) coord.attr('type','tel').attr('placeholder',phone_placeholder) ; // Tél
	else if ( select.val() == 204 ) coord.attr('type','email').attr('placeholder','xxx@yyyy.zz') ; // Mél
	else if ( select.val() == 205 )
	{
		coord.attr('type','url').attr('placeholder','http://www.xxx.zzz') ; // Url
		if ( coord.val() != '' ) row.find('small.h205').show() ;
	}
	else coord.attr('type','text').attr('placeholder','') ; // Standard

	// On ne trigger pas le changement de coordonnée lors du chargement du formulaire pour éviter d'avoir une erreur sur les champs obligatoires.
	if ( ! init ) valideChamp(coord) ;
}

function checkMC() {
	var renseignes = 0;

	var errorsContainer = jQuery('.mc-errors') ;
	errorsContainer.closest('.row').removeClass('has-error') ;
	errorsContainer.html('') ;

	jQuery('.mc-rows .mc-row input[name^="mc"]').each(function () {
		if (jQuery(this).val().trim() != '') {
			renseignes++;
		}
	});
	if (renseignes == 0) {
		errorsContainer.closest('.row').addClass('has-error') ;
		errorsContainer.html('Vous devez renseigner au moins un moyen de communication') ;
		return false;
	}
	return true;
}

jQuery(document).on('change', '.mc-rows .mc-row input[name^="mc"]', checkMC);

/**
 * 
 */

function checkTypeTarifs() {
	var rows = jQuery('form.form .tarifs-rows .tarif-row') ;
	var errorsContainer = jQuery('form.form .tarifs-errors') ;
	errorsContainer.closest('.row').removeClass('has-error') ;
	errorsContainer.html('') ;

	var erreurs = [] ;
	rows.each(function(){
		var inputs = jQuery(this).find('input') ;
		var select = jQuery(this).find('select') ;
		select.closest('.form-group').removeClass('has-error') ;
		var inputsRenseignes = false ;
		inputs.each(function(){
			if ( jQuery(this).val() != '' ) inputsRenseignes = true ;
		}) ;
		if ( inputsRenseignes && select.val() == '' )
		{
			erreurs.push('Vous devez renseigner le type de tarif') ;
			select.closest('.form-group').addClass('has-error') ;
		}
	}) ;
	if ( erreurs.length == 0 ) return true ;

	errorsContainer.closest('.row').addClass('has-error') ;
	errorsContainer.html(erreurs.join("<br />")) ;

	return erreurs ;
}
jQuery(document).on('change','form.form .tarifs-rows .tarif-row',checkTypeTarifs) ;



function checkTarifs() {
	jQuery('form.form input[name="gratuit"]').each(function(){
		jQuery(this).closest('form').find('div.champ.tarifs').toggle(( jQuery(this).is(':checked') !== true )) ;
		jQuery(this).closest('form').find('div.complement_tarif').toggle(( jQuery(this).is(':checked') !== true )) ;
		jQuery(this).closest('form').find('div.modes_paiement').toggle(( jQuery(this).is(':checked') !== true )) ;
	}) ;
}

function initForm(elem) {
	checkTarifs() ;
}

function setIndent(rowsContainer, rowsSelector) {

	if ( typeof rowsSelector === 'undefined' ) rowsSelector = 'tbody tr'

	var i = 0 ;
	rowsContainer.find(rowsSelector).each(function(){
		
		jQuery(this).find('input, select, label').each(function(){
			if ( jQuery(this).attr('name') ) {
				var name = jQuery(this).attr('name').match(/^(.*)\[([0-9]+)\](.*)/i) ;
				if ( name.length > 1 )
				{
					jQuery(this).attr('name',name[1]+'['+i+']'+name[3]) ;
					jQuery(this).attr('id',name[1]+'_'+i+'_'+name[3]) ;
				}
			}
			if ( jQuery(this).attr('for') ) {
				var forAttr = jQuery(this).attr('for').match(/^(.*)_([0-9]+)_(.*)/i) ;
				if ( forAttr != null && forAttr.length > 1 )
				{
					jQuery(this).attr('for',forAttr[1]+'_'+i+'_'+forAttr[3]) ;
				}
			}
		}) ;
	
		i++ ;
	}) ;
}

function valideTarifUnique()
{
	var selects = jQuery('form.form .tarifs-rows .tarif-row select[name^="tarifs"]') ;
	
	var used = [] ;
	selects.each(function(){
		if ( jQuery(this).val() != '' )
		{
			if ( used.indexOf(jQuery(this).val()) >= 0 )
				jQuery(this).val('') ;
			else
				used.push(jQuery(this).val()) ;
		}
	}) ;
	selects.each(function(){
		var options = jQuery(this).find('option') ;
		var select = jQuery(this) ;
		options.each(function(){
			var optVal = jQuery(this).attr('value') ;
			if ( optVal == select.val() ) ;
			else if ( used.indexOf(optVal) >= 0 )
			{
				jQuery(this).attr('disabled','disabled') ;
			}
			else
				jQuery(this).removeAttr('disabled') ;
		}) ;
	}) ;
}

/**
 * Optionnellement à l'intégration on peut rendre un contact obligatoire.
 * Dans ce cas on va contrôler à l'enregistrement si le contact est renseigné (à minima mail ou tel)
 */
function checkContacts() {
	
	var contacts = jQuery('fieldset.contacts') ;
	if ( ! contacts.hasClass('required') ) return true ;
	
	var ret = false ;

	contacts.find('input.telephone').each(function(){
		if ( jQuery(this).val() != "" ) ret = true ;
	}) ;

	contacts.find('input.mail').each(function(){
		if ( jQuery(this).val() != "" ) ret = true ;
	}) ;

	// Aucun MC de contact trouvé...
	if ( ret == false )
	{
		contacts.find('input.telephone').first().closest('.form-group').addClass('has-error') ;
		contacts.find('input.mail').first().closest('.form-group').addClass('has-error') ;
	}

	return ret ;

}
jQuery(document).on('change','fieldset.contacts',checkContacts) ;

/**
 * 17/05/2021
 * 1) ajout du ?illustrationObligatoire=1 => <fieldset class="illustrations required">
 * 2) ajout du ?illustrationMini=XX => <input type="file" minwidth="XX" />
 */
function checkFilesInput(type) {

	var dbg = true ;
	var errors = [];
	
	if (type != 'illustrations' && type != 'multimedias') {
		return false;
	}

	var fieldset = jQuery('fieldset.'+type) ;
	var inputs = fieldset.find('input[type="file"]') ;
	var rowSelector = type == 'illustrations' ? '.illustration-row' : '.multimedia-row' ;
	var errorsContainer = fieldset.find(type == 'illustrations' ? '.illustrations-errors' : '.multimedias-errors') ;

	fieldset.find(rowSelector).removeClass('has-error') ;
	fieldset.find(rowSelector + ' div.form-group').removeClass('has-error') ;
	errorsContainer.closest('.row').removeClass('has-error') ;
	errorsContainer.html('') ;

	var nbfiles = 0;
	var poidstotal = 0;

	inputs.each(function(){
		nbfiles += jQuery(this).get(0).files.length ;
		if ( jQuery(this).get(0).files.length == 1 )
		{
			/**
			 * Vérification de la taille des fichiers envoyées
			 */
			if ( typeof jQuery(this).attr('minwidth') != 'undefined' )
			{
				var minWidth = parseInt(jQuery(this).attr('minwidth')) ;
				if ( typeof jQuery(this).data('width') != 'undefined' )
				{
					if ( jQuery(this).data('width') < minWidth )
					{
						jQuery(this).closest(rowSelector).addClass('has-error') ;
						errors.push('Les '+type+' doivent faire '+minWidth+'px au minimum') ;
					}
				}
			}

			/**
			 * Vérification des copyright obligatoires
			 */
			if ( fieldset.hasClass('copyright') )
			{
				var input_copyright = jQuery(this).closest(rowSelector).find('input[name*="copyright"]') ;
				if ( input_copyright.val().trim() == "" )
				{
					input_copyright.closest('div.form-group').addClass('has-error') ;
					errors.push('Copyright obligatoire') ;
				}
			}

			/**
			 * Vérification de la taille du fichier (10 Mo max acceptés par les API)
			 * 30/05/2023 : Toujous imparfait parce qu'il faudrait vérifier le poids total des fichiers et non le poids de chaque fichier individuel
			 */
			if ( window.FileReader )
			{
				let file = jQuery(this).get(0).files[0];
				var limit = type == 'illustrations' ? 10000000 : 5000000;
				if ( file.size > limit )
				{
					jQuery(this).closest(rowSelector).addClass('has-error') ;
					errors.push('Les '+type+' doivent faire moins de '+(limit/1000000)+' Mo') ;
				}
				poidstotal += file.size;
			}

			/**
			 * Vérification du type mime
			 */
			if (window.FileReader && window.Blob) {
				let file = jQuery(this).get(0).files[0];
				if (
					(type == 'illustrations' && file.type.toString().match(/image\/(png|jpg|jpeg|gif|webp)/gi) == null)
					||
					(type == 'multimedias' && file.type.toString().match(/application\/(pdf)/gi) == null)
				) {
					jQuery(this).closest(rowSelector).addClass('has-error') ;
					errors.push('Le type d\'illustration '+file.type+' n\'est pas autorisé') ;
				}
			}
		}

	}) ;

	if (poidstotal > 10000000) {
		errors.push('L\'ensemble des fichiers joints ne doit pas dépasser 10 Mo') ;
	}

	/**
	 * Test du paramètre obligatoire (1 illustration mini)
	 */
	if ( nbfiles == 0 && fieldset.hasClass('required') )
	{
		fieldset.find(rowSelector).first().addClass('has-error') ;
		errors.push('1 '+type+' minimum') ;
	}


	if ( errors.length > 0 )
	{
		errorsContainer.closest('.row').addClass('has-error') ;
		errorsContainer.html(errors.join("<br />")) ;
	}

	return errors.length == 0 ;
}

/**
 * Comme le chargement de l'image est asynchrone on ne doit lancer le checkFilesInput qu'après
 */
jQuery(document).on('change','fieldset.illustrations input[type="file"], fieldset.multimedias input[type="file"]',function(){
	var reader = new FileReader() ;
	reader.readAsDataURL(jQuery(this).get(0).files[0]) ;
	var input = jQuery(this);
	var type = null;
	var fieldset = jQuery(this).closest('fieldset');
	if (fieldset.hasClass('illustrations')) type = 'illustrations';
	else if (fieldset.hasClass('multimedias')) type = 'multimedias';
	if (type != null) {
		reader.onload = function (e) {
			if ( type == 'illustrations' ) {
				var img = new Image();
				img.onload = function () {
					input.data('width', this.width);
					checkFilesInput(type);
				}
				img.src = e.target.result;
			} else {
				checkFilesInput(type);
			}
		};
	}
}) ;




jQuery(document).on('change', 'input[name*="copyright"]', function () {
	checkFilesInput('illustrations');
}) ;

function criteresInterditsByEr(selector) {
	
	console.log(selector, typeof jQuery(selector).val());

	var values = [];
	if (jQuery(selector).val() !== null) {
		if (typeof jQuery(selector).val() == 'object') values = jQuery(selector).val();
		else if (typeof jQuery(selector).val() == 'string') values = [jQuery(selector).val()];
	}

	console.log(values);

	if ( values.length > 0 ) {
		values.forEach(function (item) {
			if (
				typeof interdictions_elements_reference[item] != 'undefined'
				&& typeof interdictions_elements_reference[item]['interditUtilisationDe'] != 'undefined'
			) {
				jQuery('select[name!="commune"] option').each(function () {
					if (interdictions_elements_reference[item]['interditUtilisationDe'].includes(parseInt(jQuery(this).val()))) {
						jQuery(this).prop('disabled', 'disabled').attr('data-interdit', true).hide();
						if (jQuery(this).is(':selected')) {
							jQuery(this).prop('selected', false);
						}
					}
				});
				jQuery('input[type="checkbox"]').each(function () {
					if (interdictions_elements_reference[item]['interditUtilisationDe'].includes(parseInt(jQuery(this).val()))) {
						jQuery(this).on('click', function () { return false }).attr('data-interdit', true);
						jQuery(this).parent('.form-check').parent().attr('data-interdit', true);
						if (jQuery(this).is(':checked')) {
							jQuery(this).prop('checked', false).hide();
						}
					}
				});
			}
		});
	}
}

export function criteresInterdits() {

	jQuery('select option[disabled][data-interdit]').prop('disabled', false).removeAttr('data-interdit').show();
	jQuery('input[type="checkbox"][data-interdit]').off('click').removeAttr('data-interdit').show();
	jQuery('div[data-interdit]').removeAttr('data-interdit');

	if (typeof interdictions_elements_reference != 'undefined') {
		criteresInterditsByEr('select[name^="FeteEtManifestationCategorie"]');
		criteresInterditsByEr('select[name="FeteEtManifestationType"]');
	}
}

jQuery(document).on('change', 'select[name^="FeteEtManifestationCategorie"], select[name="FeteEtManifestationType"]', criteresInterdits);

jQuery(document).on('change', 'input[type="file"]', function () {
	jQuery(this).closest('.inputFile').toggleClass('hasFile', jQuery(this).val() != '');
});

jQuery(document).on('click', '.removeFile', function () {
	const input = jQuery(this).closest('.inputFile').find('input[type="file"]');
	const clone = input.clone(false);
	clone.val('');
	input.replaceWith(clone);
	jQuery(this).closest('.inputFile').removeClass('hasFile');
});


// https://getbootstrap.com/docs/5.2/components/tooltips/#examples
jQuery(function() {
	const bootstrap = window.bootstrap || global.bootstrap;
	if (bootstrap && bootstrap.Tooltip) {
		const tooltipTriggerList = document.querySelectorAll('.fa-info-circle');
		const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
	}
});




export function recaptchaKo(){
	jQuery('form.form input.btn-submit').closest('div.form-group').hide() ;
	jQuery('form.form div#recaptcha p').show() ;
}

export function recaptchaOk()
{
	jQuery('form.form input.btn-submit').closest('div.form-group').show() ;
	jQuery('form.form div#recaptcha p').hide() ;
}

export function faker() {
	var cd = new Date() ;
	var td = cd.getDate()+'/'+(cd.getMonth()+1)+' - '+cd.getHours()+':'+cd.getMinutes() ;
	jQuery('input[name="nom"]').val('Test '+td) ;
	jQuery('input[type="tel"]').val('01 23 45 67 89') ;
	jQuery('select[name="portee"]').val('2354') ;
	//jQuery('select[name="commune"]').val('1408|03510|Molinet|03173') ;
	jQuery('select[name="commune"]').val('14707|37260|Villeperdue|37278') ;
	jQuery("form.form select.select2").trigger("change");

	var d5 = new Date(new Date().getTime()+(5*24*60*60*1000));
	var d = d5.toISOString().match('([0-9]{4}-[0-9]{2}-[0-9]{2})');
	jQuery('input[name="date[0][debut]"').val(d[0]) ;
	jQuery('input[name="date[0][fin]"').val(d[0]) ;

	jQuery('textarea[name="descriptifCourt"]').val(td) ;
}
