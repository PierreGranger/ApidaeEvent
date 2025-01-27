<fieldset class="contacts<?php if (isset($_GET['contactObligatoire']) && $_GET['contactObligatoire']) echo ' required'; ?>">

        <legend><?php __('Contacts organisateurs') ; ?></legend>

        <div class="alert alert-warning" role="alert">
        <?php __('<strong>Merci de préciser au moins une adresse mail (de préférence) et/ou un numéro de téléphone</strong> : en cas de questions, nous pourrons prendre contact avec l\'organisateur grâce à ces informations.') ; ?>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th><?php __('Fonction') ; ?></th>
                        <th><?php __('Prénom') ; ?></th>
                        <th><?php __('Nom') ; ?></th>
                        <th><?php __('Mail') ; ?></th>
                        <th><?php __('Téléphone') ; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $types = $apidaeEvent->getElementsReferenceByType('ContactFonction');
                    for ($i = 0; $i < 1; $i++) {
                        echo "\n\t\t\t\t\t\t" . '<tr>';
                        echo '<td></td>';
                        echo '<td>';
                        echo '<select class="form-select" name="contact[' . $i . '][fonction]">';
                        echo '<option value="">-</option>';
                        foreach ($types as $type) {
                            echo '<option value="' . $type['id'] . '"';
                            if (@$post['contact'][$i]['fonction'] == $type['id']) echo ' selected="selected" ';
                            echo '>';
                            echo $apidaeEvent->libelleEr($type);
                            echo '</option>';
                        }
                        echo '</select>';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<input class="form-control" type="text" name="contact[' . $i . '][prenom]" value="' . htmlspecialchars(@$post['contact'][$i]['prenom']) . '" />';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<input class="form-control col" type="text" name="contact[' . $i . '][nom]" value="' . htmlspecialchars(@$post['contact'][$i]['nom']) . '" />';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<input class="form-control mail" type="text" name="contact[' . $i . '][mail]" value="' . htmlspecialchars(@$post['contact'][$i]['mail']) . '" placeholder="xxx@yyyy.zz" />';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<input class="form-control telephone" type="text" name="contact[' . $i . '][telephone]" value="' . htmlspecialchars(@$post['contact'][$i]['telephone']) . '" placeholder="' . $phone_placeholder . '" />';
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '<tr>';
                    echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', __('Ajouter un contact',false), $icon_plus) . '</td>';
                    echo '</tr>';
                    ?>
                </tbody>
            </table>
        </div>

    </fieldset>