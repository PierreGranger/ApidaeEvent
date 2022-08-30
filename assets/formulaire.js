
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
	

	if ( jQuery('select[name="organisateur"]').length > 0 )
	{
		
	}

}) ;

jQuery(document).on('click','form.form .btn-submit',function(){
	jQuery(this).closest('form.form').submit() ;
}) ;

/**
 * à l'enregistrement on va parcourir tous les champs pour les vérifier.
 * 
 */
jQuery(document).on('submit','form.form',function(e){

	var ok = true ;
	var firstError = null ;

	jQuery(this).find('select, input, textarea').each(function(){
		var okChamp = valideChamp(jQuery(this),jQuery(this).closest('tr').find('select').val()) ;
		jQuery(this).closest('.form-group').toggleClass('has-error',!okChamp) ;
		if ( ! okChamp )
		{
			ok = false ;
			if ( firstError == null ) firstError = jQuery(this) ;
		}
	}) ;

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

	var erreurIllustrations = checkIllustrations() ;
	if ( erreurIllustrations !== true )
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
	ligne.insertBefore(tr) ;
	ligne.find('td').first().addClass('moins').html(icon_moins) ;
	var champs = ligne.find('input, select') ;
	champs.each(function(i,v){
		jQuery(this).removeAttr('required') ;
		jQuery(this).val('') ;
		if ( jQuery(this).closest('table').hasClass('mc') ) jQuery(this).attr('placeholder','') ;
		jQuery(this).removeClass('hasDatepicker hasTimepicker').attr('id',null) ;
	}) ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
	valideTarifUnique() ;
}) ;

jQuery(document).on('click','table td.moins',function(){
	jQuery(this).closest('tr').remove() ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
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
	jQuery(this).closest('.form-group').toggleClass('has-error',!valideChamp(jQuery(this))) ;
}) ;

jQuery(document).on('change','div.tarifs select[name^="tarifs"]',function(){
	valideTarifUnique() ;
}) ;

function valideChamp(champ)
{
	var val = champ.val() ;
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
		else if ( devise == 'CHF' || devise == 'CFP' )
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

	//elem.find('input.date').not('.hasDatepicker').datepicker(optsDate).addClass('hasDatepicker').prop('min',today) ;
	//elem.find('input.time').not('.hasTimepicker').timepicker(optsTime).addClass('hasTimepicker') ;
	//jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "fr" ] );

	checkTarifs() ;

}

function setIndent(table) {
	var i = 0 ;
	table.find('tbody tr').each(function(){
		jQuery(this).find('input, select').each(function(){
			var trouve = jQuery(this).attr('name').match(/^(.*)\[([0-9]+)\](.*)/i) ;
			if ( trouve.length > 1 )
			{
				jQuery(this).attr('name',trouve[1]+'['+i+']'+trouve[3]) ;
			}
		}) ;
		i++ ;
	}) ;
}

function recaptchaKo(){
	jQuery('form.form input.btn-submit').closest('div.form-group').hide() ;
	jQuery('form.form div#recaptcha p').show() ;
}

function recaptchaOk()
{
	jQuery('form.form input.btn-submit').closest('div.form-group').show() ;
	jQuery('form.form div#recaptcha p').hide() ;
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
function checkIllustrations() {

	var dbg = true ;
	var errors = [] ;

	var fieldset = jQuery('fieldset.illustrations') ;
	var inputs = fieldset.find('input[type="file"]') ;
	var tfoot = fieldset.find('tfoot tr td') ;

	fieldset.find('tbody tr').removeClass('has-error') ;
	fieldset.find('tbody tr div.form-group').removeClass('has-error') ; // Retrait des erreurs pour les copyright
	tfoot.closest('tr').removeClass('has-error') ;
	tfoot.html('') ;

	var nbfiles = 0 ;
	inputs.each(function(){
		nbfiles += jQuery(this).get(0).files.length ;
		if ( jQuery(this).get(0).files.length == 1 )
		{
			/**
			 * Vérification de la taille des illustrations envoyées
			 */
			if ( typeof jQuery(this).attr('minwidth') != 'undefined' )
			{
				var minWidth = parseInt(jQuery(this).attr('minwidth')) ;
				if ( typeof jQuery(this).data('width') != 'undefined' )
				{
					if ( jQuery(this).data('width') < minWidth )
					{
						jQuery(this).closest('tr').addClass('has-error') ;
						errors.push('Les illustrations doivent faire '+minWidth+'px au minimum') ;
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
			 */
			if ( window.FileReader )
			{
				let file = jQuery(this).get(0).files[0] ;
				if ( file.size > 10000000 )
				{
					jQuery(this).closest('tr').addClass('has-error') ;
					errors.push('Les illustrations doivent faire moins de 10 Mo') ;
				}
			}

			/**
			 * Vérification du type mime
			 */
			if (window.FileReader && window.Blob) {
				let file = jQuery(this).get(0).files[0] ;
				if ( file.type.toString().match(/image\/(png|jpg|jpeg|gif)/gi) == null ) {
					jQuery(this).closest('tr').addClass('has-error') ;
					errors.push('Le type d\'illustration '+file.type+' n\'est pas autorisé') ;
				}
			}
		}
	}) ;

	/**
	 * Test du paramètre obligatoire (1 illustration mini)
	 */
	if ( nbfiles == 0 && fieldset.hasClass('required') )
	{
		fieldset.find('tbody tr').first().addClass('has-error') ;
		errors.push('1 illustration minimum') ;
	}


	if ( errors.length > 0 )
	{
		tfoot.closest('tr').addClass('has-error') ;
		tfoot.html(errors.join("<br />")) ;
	}

	return errors.length == 0 ;
}

/**
 * Comme le chargement de l'image est asynchrone on ne doit lancer le checkIllustrations qu'après
 */
//jQuery(document).on('change','fieldset.illustrations',checkIllustrations) ;
jQuery(document).on('change','fieldset.illustrations input[type="file"]',function(){
	var reader = new FileReader() ;
	reader.readAsDataURL(jQuery(this).get(0).files[0]) ;
	var input = jQuery(this) ;
	reader.onload = function(e) {
		var img = new Image() ;
		img.onload = function() {
			input.data('width',this.width) ;
			checkIllustrations() ;
		}
		img.src = e.target.result ;
	} ;
}) ;

jQuery(document).on('change','input[name*="copyright"]',checkIllustrations) ;










function faker() {
	var cd = new Date() ;
	var td = cd.getDate()+'/'+(cd.getMonth()+1)+' - '+cd.getHours()+':'+cd.getMinutes() ;
	jQuery('input[name="nom"]').val('Test '+td) ;
	jQuery('input[type="tel"]').val('01 23 45 67 89') ;
	jQuery('select[name="portee"]').val('2354') ;
	jQuery('select[name="commune"]').val('1408|03510|Molinet|03173') ;
	jQuery("form.form select.chosen").trigger("chosen:updated");

	var d5 = new Date(new Date().getTime()+(5*24*60*60*1000));
	var d = d5.toISOString().match('([0-9]{4}-[0-9]{2}-[0-9]{2})');
	jQuery('input[name="date[0][debut]"').val(d[0]) ;
	jQuery('input[name="date[0][fin]"').val(d[0]) ;

	jQuery('textarea[name="descriptifCourt"]').val(td) ;
}