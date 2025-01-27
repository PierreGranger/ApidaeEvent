<?php

    use Gettext\Loader\PoLoader;
    use Gettext\Generator\PoGenerator;
    use Gettext\Generator\MoGenerator;
    use DeepL\Translator;
    use PierreGranger\ApidaeEvent;

    $root = realpath(dirname(__FILE__)).'/../' ;

    require_once($root.'vendor/autoload.php') ;
    require_once($root.'src/requires.inc.php') ;

    $translator = new Translator($keyDeepl) ;

    $poLoader = new PoLoader() ;
    $poGenerator = new PoGenerator() ;
    $moGenerator = new MoGenerator() ;

    foreach ( ApidaeEvent::ACCEPTED_LANGUAGES as $lang => $lang_details ) {

        $po_dir = $root.'locale/'.$lang_details['locale'].'/LC_MESSAGES/' ;

        if ( ! file_exists($po_dir.'event.po') || ! isset($lang_details['deepL']) ) {
            echo str_repeat('*****'.PHP_EOL,10). $lang_details['locale'].' non traité'.PHP_EOL . str_repeat('*****'.PHP_EOL,10) ;
            continue ;
        }
        $translations = $poLoader->loadFile($po_dir.'event.po') ;
        
        foreach ( $translations->getTranslations() as $translation ) {
            //var_dump($translation) ;
            echo '******************'.PHP_EOL ;
            echo $translation->getOriginal() . PHP_EOL ;
            $translated = $translator->translateText(
                $translation->getOriginal(),
                'fr',
                preg_replace('#_#','-',$lang_details['locale'])
            ) ;
            echo '------->'.PHP_EOL ;
            echo $translated . PHP_EOL ;

            $translation->translate($translated) ;
        }

        $poGenerator->generateFile($translations, $po_dir.'/event.po') ;
        $moGenerator->generateFile($translations, $po_dir.'/event.mo') ;
    }
