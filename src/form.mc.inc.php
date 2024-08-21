<fieldset>

        <legend><?php __('Moyens de communication') ; ?></legend>

        <div class="alert alert-warning" role="alert">
            <?php __('Merci de préciser au moins un moyen de communication (Mail, téléphone...) : ils seront diffusés sur les supports de communications (sites web, brochures...)') ; ?>
        </div>

        <div class="table-responsive">
            <table class="table mc">
                <thead>
                    <tr>
                        <th></th>
                        <th class="required"><?php __('Type') ; ?></th>
                        <th class="required"><?php __('Coordonnée') ; ?></th>
                        <th><?php __('Complément') ; ?></th>
                    </tr>
                </thead>
                <tfoot><tr><td colspan="4"></td></tr></tfoot>
                <tbody>
                    <?php

                    $types = $apidaeEvent->getElementsReferenceByType('MoyenCommunicationType', array('include' => $configApidaeEvent['types_mcs']));

                    $nb = 3;
                    if (isset($post['mc'])) $nb = sizeof($post['mc']);

                    for ($i = 0; $i < $nb; $i++) {
                        echo "\n\t\t\t\t\t\t" . '<tr>';
                        echo '<td></td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<select class="form-control" name="mc[' . $i . '][type]"';
                        echo '>';
                        echo '<option value="">-</option>';
                        foreach ($types as $type) {
                            echo '<option value="' . $type['id'] . '"';
                            if (isset($post['mc'])) {
                                if (@$post['mc'][$i]['type'] == $type['id'])
                                    echo ' selected="selected';
                            } else {
                                if (
                                    ($i == 0 && $type['id'] == 201) // Téléphone
                                    || ($i == 1 && $type['id'] == 204) // Mél
                                    || ($i == 2 && $type['id'] == 205) // Site web
                                )
                                    echo ' selected="selected" ';
                            }
                            echo '>';
                            echo $apidaeEvent->libelleEr($type);
                            echo '</option>';
                        }
                        echo '</select>';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<input class="form-control" type="text" name="mc[' . $i . '][coordonnee]" value="' . htmlentities(@$post['mc'][$i]['coordonnee']) . '" ';
                        echo '/>';
                        echo '<small style="display:none;" class="help h205">http(s)://...</small>';
                        echo '</div>';
                        echo '</td>';
                        echo '<td>';
                        echo '<div class="form-group">';
                        echo '<input class="form-control" type="text" name="mc[' . $i . '][observations]" value="' . htmlentities(@$post['mc'][$i]['observations']) . '" />';
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '<tr>';
                    echo '<td class="plus" colspan="99">' . preg_replace('/##LIBELLE##/', __('Ajouter une ligne',false), $icon_plus) . '</td>';
                    echo '</tr>';
                    ?>
                </tbody>
            </table>
        </div>
    </fieldset>