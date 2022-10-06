<?php
        if (isset($_GET['clientele'])) {
            $labelClientele = 'Types de Clientèle';
            $params = array(
                'presentation' => 'select',
                //'include' => Array(6486) // Pass sanitaire obligatoire
                /*
                         3734 Spécial LGBT	10.02.72	Offres adaptées à des clientèles spécifiques		6	
                        3737 Réservé à un public majeur	10.02.75	Offres adaptées à des clientèles spécifiques		41	
                        579 Spécial célibataires	10.02.54	Offres adaptées à des clientèles spécifiques		42	
                        594 Spécial enfants	10.02.49	Offres adaptées à des clientèles spécifiques		43	
                        4908 Spécial étudiants		Offres adaptées à des clientèles spécifiques		44	
                        4813 Spécial retraités		Offres adaptées à des clientèles spécifiques		45	
                        5416 Spécial sportifs		Offres adaptées à des clientèles spécifiques		46	
                        496 Spécial adolescents	10.02.01	Offres adaptées à des clientèles spécifiques		47	
                        509 Spécial amoureux	10.02.08	Offres adaptées à des clientèles spécifiques		48	
                        513 Spécial famille avec enfants 	10.02.11	Offres adaptées à des clientèles spécifiques
                        504 Cavaliers	10.02.06	Clientèles pratiquant une activité spécifique		3	
                        511 Curistes	10.02.09	Clientèles pratiquant une activité spécifique		4	
                        512 Cyclistes	10.02.10	Clientèles pratiquant une activité spécifique		5	
                        565 Motards	10.02.30	Clientèles pratiquant une activité spécifique		7	
                        591 Naturistes	10.02.61	Clientèles pratiquant une activité spécifique		8	
                        566 Pêcheurs	10.02.31	Clientèles pratiquant une activité spécifique		9	
                        522 Pèlerins	10.02.20	Clientèles pratiquant une activité spécifique
                        564 Pratiquants de sports d'eaux vives	10.02.29	Clientèles pratiquant une activité spécifique		22	
                        558 Randonneurs	10.02.23	Clientèles pratiquant une activité spécifique		23	
                        4668 Randonneurs à raquettes acceptés		Clientèles pratiquant une activité spécifique		24	
                        563 VTTistes	10.02.28	Clientèles pratiquant une activité spécifique	
                      */
                'include' => [
                    3734, 3737, 579, 594, 4908, 4813, 5416, 496, 509, 513, 504, 511, 512, 565, 591, 566, 522, 564, 558, 4668, 563
                ]
            );
            ?>
            <div class="<?= $class_line ; ?> prestations-typesClientele">
                <label class="<?php echo $class_label; ?> col-form-label"><?php echo $labelClientele; ?></label>
                <div class="<?php echo $class_champ; ?>">
                    <?php echo $apidaeEvent->formHtmlCC('TypeClientele', $params, @$post['TypeClientele']); ?>
                </div>
            </div>
            <?php
        }
        ?>