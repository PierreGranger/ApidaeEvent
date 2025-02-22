<?php
    $classes = ['form-group', 'illustrations'];
    if (isset($_GET['illustrationObligatoire']) && $_GET['illustrationObligatoire']) $classes[] = 'required';
    if (isset($_GET['copyright']) && $_GET['copyright']) $classes[] = 'copyright';
    ?>
    <fieldset class="<?php echo implode(' ', $classes); ?>">
        <legend><?php __('Photos') ; ?></legend>
        <div class="alert alert-warning" role="alert">
            <?php __('Vos photos doivent être libres de droit et de bonne qualité') ; ?> (
            <?php if (isset($_GET['illustrationMini'])) { ?>
                <strong><?php echo $_GET['illustrationMini']; ?><?php __('px de largeur minimum') ; ?></strong>
            <?php } else { ?>
                <?php __('<strong>si possible, 1920px de largeur minimum</strong>') ; ?>
            <?php } ?> <?php __('et <strong>10 Mo maximum</strong>), aux formats png ou jpg/jpeg.') ; ?>
            <br /><?php __('Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...) : <strong>assurez-vous d\'avoir tous les droits nécessaires</strong>, et précisez le Copyright si besoin.') ; ?>
            <br />
            <a href="https://aide.apidae-tourisme.com/hc/fr/articles/360000825391-Saisie-l-onglet-multimédias-Zoom-sur-les-illustrations#tailleimages" target="_blank"><i class="fas fa-info-circle"></i> <?php __('Plus d\'informations ici') ; ?>.</a>
        </div>
        <div class="table-responsive">
            <table class="table photos">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php __('Votre photo') ; ?></th>
                        <th><?php __('Titre') ; ?></th>
                        <th><?php __('Copyright') ; ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    for ($i = 0; $i < 1; $i++) {
                        echo "\n\t\t\t\t\t\t" . '<tr>';
                            echo '<td></td>';
                            echo '<td>';
                                echo '<div class="form-group">' ;
                                    echo '<input class="form-control" type="file" name="illustrations[' . $i . ']" accept="image/*" ';
                                    if (isset($_GET['illustrationMini']) && (int)$_GET['illustrationMini'] > 0 && (int)$_GET['illustrationMini'] <= 2000)
                                        echo 'minwidth="' . (int)$_GET['illustrationMini'] . '" ';
                                    echo '/>';
                                echo '</div>' ;
                            echo '</td>';
                            echo '<td>' ;
                                echo '<div class="form-group">' ;
                                    echo '<input class="form-control" type="text" name="illustrations[' . $i . '][legende]" value="' . htmlspecialchars(@$post['illustrations'][$i]['legende']) . '" /></td>';
                                echo '</div>' ;
                            echo '<td>';
                                echo '<div class="form-group">';
                                    echo '<input class="form-control" type="text" name="illustrations[' . $i . '][copyright]" value="' . htmlspecialchars(@$post['illustrations'][$i]['copyright']) . '" />';
                                echo '</div>';
                            echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                    <tr>
                        <td class="plus" colspan="99"><?php echo preg_replace('/##LIBELLE##/', __('Ajouter une photo',false), $icon_plus); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </fieldset>

    <?php if (isset($_GET['mm']) && $_GET['mm'] == 1) { ?>
        <fieldset class="multimedias">
            <legend><?php __('Multimédias') ; ?></legend>
            <div class="alert alert-warning" role="alert">
                <?php __('Vous pouvez ajouter ci-dessous des fichiers PDF si nécessaire (si vous avez un programme par exemple).
                <br />Une fois publiées, elles pourront être diffusées sur différents supports (sites Internet, brochures...) : <strong>assurez-vous d\'avoir tous les droits nécessaires</strong>, et précisez le Copyright si besoin.
                <br />Les documents ajoutés ne doivent pas dépasser les 5 Mo au total.') ; ?>
            </div>
            <div class="table-responsive">
                <table class="table photos">
                    <thead>
                        <tr>
                            <th></th>
                            <th><?php __('Votre fichier') ; ?></th>
                            <th><?php __('Titre') ; ?></th>
                            <th><?php __('Copyright') ; ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php
                        for ($i = 0; $i < 1; $i++) {
                            echo "\n\t\t\t\t\t\t" . '<tr>';
                            echo '<td></td>';
                            echo '<td>';
                                echo '<div class="form-group">' ;
                                    echo '<input class="form-control" type="file" name="multimedias[' . $i . ']" ' ;
                                        //echo 'value="' . htmlspecialchars(@$post['multimedias'][$i]) . '" ' ;
                                    echo 'accept="' . implode(',', $configApidaeEvent['mimes_multimedias']) . '" />';
                                echo '</div>' ;
                            echo '</td>';
                            echo '<td>' ;
                                echo '<div class="form-group">' ;
                                    echo '<input class="form-control" type="text" name="multimedias[' . $i . '][legende]" value="' . htmlspecialchars(@$post['multimedias'][$i]['legende']) . '" />' ;
                                echo '</div>' ;
                            echo '</td>';
                            echo '<td>' ;
                                echo '<div class="form-group">' ;
                                    echo '<input class="form-control" type="text" name="multimedias[' . $i . '][copyright]" value="' . htmlspecialchars(@$post['multimedias'][$i]['copyright']) . '" />' ;
                                echo '</div>' ;
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                        <tr>
                            <td class="plus" colspan="99"><?php echo preg_replace('/##LIBELLE##/', __('Ajouter un fichier',false), $icon_plus); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>
    <?php } ?>