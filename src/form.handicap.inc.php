<?php if (isset($_GET['TourismeAdapte']) && $_GET['TourismeAdapte'] == 1) { ?>

    <?php
    // https://apidae-tourisme.zendesk.com/agent/tickets/5997
    $params = array(
        'presentation' => 'checkbox',
        'include' => array(3651, 3653, 3652, 3674, 3675, 3943, 3676, 1191, 1196, 4217, 3666, 1199, 4219)
    );
    ?>
    <div class="<?= $class_line ; ?> TourismeAdapte">
        <label class="<?php echo $class_label; ?> col-form-label">
            Accessibilité
            <br /><small class="form-text text-muted">Accueil des personnes en situation de handicap</small>
        </label>
        <div class="<?php echo $class_champ; ?>">
            <?php echo $apidaeEvent->formHtmlCC('TourismeAdapte', $params, @$post['TourismeAdapte']); ?>
        </div>
    </div>

<?php } ?>