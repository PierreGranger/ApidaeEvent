
jQuery(function() {
	jQuery('.chosen-select').chosen({
		disable_search_threshold: 10
	});
});

var optsDate = {
	'dateFormat' : 'dd/mm/yy',
	'minDate' : '+1d',
	firstDay:1,
} ;
var optsTime = {
	'scrollDefault': '09:00',
	'timeFormat': 'H:i'
} ;

var today = new Date() ;

jQuery(function(){

	//jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "fr" ] );

	jQuery('form.form select.chosen').each(function(){
		var params = {
			include_group_label_in_selected : true,
			search_contains:true,
			width:'100%',
			no_results_text:'Aucun résultat trouvé'
		} ;
		if ( typeof jQuery(this).data('max_selected_options') == 'number' )
			params['max_selected_options'] = jQuery(this).data('max_selected_options') ;
		jQuery(this).chosen(params) ;
	}) ;

	initForm(jQuery('form.form')) ;

	jQuery('select[name$="[type]"]').each(function(){
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

	var ok = true ;
	var firstError = null ;

	jQuery(this).find('select, input, textarea').each(function(){
		var okChamp = valideChamp(jQuery(this), jQuery(this).closest('tr').find('select').val());
		jQuery(this).closest('.form-group, div').toggleClass('has-error',!okChamp) ;
		if ( ! okChamp )
		{
			ok = false ;
			if ( firstError == null ) firstError = jQuery(this) ;
		}
	});
	
	var erreurMC = checkMC() ;
	if ( erreurMC !== true )
	{
		ok = false ;
	}

	var erreurTarif = checkTypeTarifs() ;
	if ( erreurTarif !== true )
	{
		ok = false ;
	}

	var erreurContacts = checkContacts() ;
	if ( erreurContacts !== true )
	{
		ok = false ;
	}

	var erreurIllustrations = checkFilesInput('illustrations') ;
	if ( erreurIllustrations !== true )
	{
		ok = false ;
	}

	var erreurMultimedias = checkFilesInput('multimedias') ;
	if ( erreurMultimedias !== true )
	{
		ok = false ;
	}

	if ( ok === true )
	{
		jQuery(this).css('opacity',0.5) ;
		jQuery('input.btn-submit').closest('div').replaceWith('<div class="alert alert-warning loading">Formulaire en cours d\'enregistrement, veuillez patienter...</div>') ;
		return true ;
	}
	else
	{
		if ( firstError !== null )
		{
			var disp = firstError.is(':hidden') ;
			if ( disp ) firstError.show() ;
			firstError.focus() ;
			if ( disp ) firstError.hide() ;
		}
		e.preventDefault() ;
		e.stopImmediatePropagation();
		alert('Votre formulaire comporte des erreurs : merci de vérifier les champs encadrés en rouge') ;
		return false ;
	}

}) ;


// Clone une ligne d'une table.
jQuery(document).on('click','table td.plus .btn',function(){
	var ligne = jQuery(this).closest('tbody').find('tr').first().clone() ;
	var tr = jQuery(this).closest('tr') ;
	var table = jQuery(this).closest('table') ;
	ligne.insertBefore(tr) ;
	ligne.find('td').first().addClass('moins').html(icon_moins) ;
	var champs = ligne.find('input, select') ;
	champs.each(function(i,v){
		jQuery(this).removeAttr('required') ;
		jQuery(this).val('') ;
		if ( table.hasClass('mc') ) jQuery(this).attr('placeholder','') ;
		jQuery(this).removeClass('hasDatepicker hasTimepicker').attr('id',null) ;
	}) ;
	ligne.find('.description').each(function() {
		jQuery(this).html('') ;
	})
	setIndent(table) ;
	initForm(table) ;
	valideTarifUnique() ;
}) ;

jQuery(document).on('click','table td.moins',function(){
	jQuery(this).closest('tr').remove() ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
}) ;


// Clone row in multirows.
jQuery(document).on('click','div.multirows .plus .btn',function(){
	let plus = jQuery(this) ;
	let multirows = plus.closest('.multirows') ;
	let rows = multirows.find('.rows') ;
	let row = rows.find('.row').first().clone() ;
	row.find('div').first().addClass('moins').html(icon_moins) ;
	rows.append(row) ;
	let champs = row.find('input, select') ;
	champs.each(function(i,v){
		jQuery(this).removeAttr('required') ;
		jQuery(this).val('') ;
		if ( rows.hasClass('mc') ) jQuery(this).attr('placeholder','') ;
		jQuery(this).removeClass('hasDatepicker hasTimepicker').attr('id',null) ;
	}) ;
	setIndent(rows,'.row') ;
	initForm(rows) ;
	valideTarifUnique() ;
}) ;

jQuery(document).on('click','div.multirows .moins',function(){
	jQuery(this).closest('.row').remove() ;
	setIndent(jQuery(this).closest('.rows'),'.row') ;
	initForm(jQuery(this).closest('.rows')) ;
}) ;





jQuery(document).on('click','div.date span.input-group-addon',function(){
	jQuery(this).closest('div').find('button').trigger('click') ;
}) ;

jQuery(document).on('click','div.time span.input-group-addon',function(){
	jQuery(this).closest('div').find('input').focus() ;
}) ;










jQuery(document).on('change','select[name$="[type]"]',function(){
	selectChange(jQuery(this)) ;
}) ;

jQuery(document).on('change','input[type="url"]',function(){
	selectChange(jQuery(this).closest('tr').find('select[name$="[type]"]',true)) ;
}) ;

jQuery(document).on('change','form.form input[name="gratuit"]',function(){
	checkTarifs();
}) ;

jQuery(document).on('change focusout','form.form select, form.form input, form.form textarea',function(){
	jQuery(this).closest('.form-group, div').toggleClass('has-error',!valideChamp(jQuery(this))) ;
}) ;

jQuery(document).on('change','div.tarifs select[name^="tarifs"]',function(){
	valideTarifUnique() ;
}) ;

function valideChamp(champ)
{
	var val = champ.val();
	
	if (champ.is(':checkbox') && ! champ.is(':checked')) {
		val = '';
	}

	var type = null ;
	if ( typeof champ.attr('name') !== 'undefined' && champ.attr('name').match(/\[coordonnee\]$/) )
		type = champ.closest('tr').find('select').val() ;

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
	var coord = select.closest('tr').find('input[name$="[coordonnee]"]') ;
	coord.closest('tr').find('small.h205').hide() ;

	if ( select.val() == 201 ) coord.attr('type','tel').attr('placeholder',phone_placeholder) ; // Tél
	else if ( select.val() == 204 ) coord.attr('type','email').attr('placeholder','xxx@yyyy.zz') ; // Mél
	else if ( select.val() == 205 )
	{
		coord.attr('type','url').attr('placeholder','http://www.xxx.zzz') ; // Url
		if ( coord.val() != '' ) coord.closest('tr').find('small.h205').show() ;
	}
	else coord.attr('type','text').attr('placeholder','') ; // Standard

	// On ne trigger par le changement de coordonnée lors du chargement du formulaire pour éviter d'avoir une erreur sur les champs obligatoires.
	if ( ! init ) valideChamp(coord) ;
}

function checkMC() {
	var renseignes = 0;

	var tfoot = jQuery('table.mc').find('tfoot tr td') ;
	tfoot.closest('tr').removeClass('has-error') ;
	tfoot.html('') ;

	jQuery('table.mc tbody tr input[name^="mc"]').each(function () {
		if (jQuery(this).val().trim() != '') {
			renseignes++;
		}
	});
	if (renseignes == 0) {
		tfoot.closest('tr').addClass('has-error') ;
		tfoot.html('Vous devez renseigner au moins un moyen de communication') ;
		return false;
	}
	return true;
}

jQuery(document).on('change', 'table.mc tbody tr input[name^="mc"]', checkMC);

/**
 * 
 */

function checkTypeTarifs() {
	var trs = jQuery('form.form div.tarifs table tbody tr') ;
	
	var tfoot = trs.closest('table').find('tfoot tr td') ;
	tfoot.closest('tr').removeClass('has-error') ;
	tfoot.html('') ;

	var erreurs = [] ;
	trs.each(function(){
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

	tfoot.closest('tr').addClass('has-error') ;
	tfoot.html(erreurs.join("<br />")) ;

	return erreurs ;
}
jQuery(document).on('change','form.form div.tarifs table tbody tr',checkTypeTarifs) ;



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
	var selects = jQuery('form.form div.tarifs table tbody tr select[name^="tarifs"]') ;
	
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
	var tfoot = fieldset.find('tfoot tr td') ;

	fieldset.find('tbody tr').removeClass('has-error') ;
	fieldset.find('tbody tr div.form-group').removeClass('has-error') ; // Retrait des erreurs pour les copyright
	tfoot.closest('tr').removeClass('has-error') ;
	tfoot.html('') ;

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
						jQuery(this).closest('tr').addClass('has-error') ;
						errors.push('Les '+type+' doivent faire '+minWidth+'px au minimum') ;
					}
				}
			}

			/**
			 * Vérification des copyright obligatoires
			 */
			if ( fieldset.hasClass('copyright') )
			{
				var input_copyright = jQuery(this).closest('tr').find('input[name*="copyright"]') ;
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
					jQuery(this).closest('tr').addClass('has-error') ;
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
					(type == 'illustrations' && file.type.toString().match(/image\/(png|jpg|jpeg|gif)/gi) == null)
					||
					(type == 'multimedias' && file.type.toString().match(/application\/(pdf)/gi) == null)
				) {
					jQuery(this).closest('tr').addClass('has-error') ;
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
		fieldset.find('tbody tr').first().addClass('has-error') ;
		errors.push('1 '+type+' minimum') ;
	}


	if ( errors.length > 0 )
	{
		tfoot.closest('tr').addClass('has-error') ;
		tfoot.html(errors.join("<br />")) ;
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
	
	var values = [];
	if (typeof jQuery(selector).val() == 'object') values = jQuery(selector).val();
	else if(typeof jQuery(selector).val() == 'string') values = [jQuery(selector).val()];

	if ( values.length > 0 ) {
		values.forEach(function (item) {
			if (
				typeof interdictions_elements_reference[item] != 'undefined'
				&& typeof interdictions_elements_reference[item]['interditUtilisationDe'] != 'undefined'
			) {
				jQuery('select option').each(function () {
					if (interdictions_elements_reference[item]['interditUtilisationDe'].includes(parseInt(jQuery(this).val()))) {
						jQuery(this).prop('disabled', 'disabled').attr('data-interdit', true);
						if (jQuery(this).is(':selected')) {
							jQuery(this).prop('selected', false);
						}
					}
				});
				jQuery('input[type="checkbox"]').each(function () {
					if (interdictions_elements_reference[item]['interditUtilisationDe'].includes(parseInt(jQuery(this).val()))) {
						jQuery(this).on('click', function () { return false }).attr('data-interdit', true);
						jQuery(this).closest('div').find('label').attr('data-interdit', true);
						if (jQuery(this).is(':checked')) {
							jQuery(this).prop('checked', false);
						}
					}
				});
			}
		});
	}
}

export function criteresInterdits() {

	jQuery('select option[disabled][data-interdit]').prop('disabled', false).removeAttr('data-interdit');
	jQuery('input[type="checkbox"][data-interdit]').off('onclick').removeAttr('data-interdit');
	jQuery('label[data-interdit]').removeAttr('data-interdit');

	if (typeof interdictions_elements_reference != 'undefined') {
		criteresInterditsByEr('select[name^="FeteEtManifestationCategorie"]');
		criteresInterditsByEr('select[name="FeteEtManifestationType"]');
	}
}

jQuery(document).on('change', 'select[name^="FeteEtManifestationCategorie"], select[name="FeteEtManifestationType"]', criteresInterdits);







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
	jQuery("form.form select.chosen").trigger("chosen:updated");

	var d5 = new Date(new Date().getTime()+(5*24*60*60*1000));
	var d = d5.toISOString().match('([0-9]{4}-[0-9]{2}-[0-9]{2})');
	jQuery('input[name="date[0][debut]"').val(d[0]) ;
	jQuery('input[name="date[0][fin]"').val(d[0]) ;

	jQuery('textarea[name="descriptifCourt"]').val(td) ;
}
