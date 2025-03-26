<?php
/**
 * Script de test pour insérer un bloc avec des éléments dans PrettyBlocks
 * 
 * À exécuter depuis la racine de PrestaShop avec:
 * php test-prettyblocks-elements.php
 */

// Initialisation de PrestaShop
include('../../config/config.inc.php');
include('../../init.php');

// Vérifier que le module PrettyBlocks est installé
if (!Module::isInstalled('prettyblocks')) {
    die('Le module PrettyBlocks n\'est pas installé.');
}

// Contexte
$id_lang = (int)Context::getContext()->language->id;
$id_shop = (int)Context::getContext()->shop->id;

// Créer un nouveau bloc
$block = new PrettyBlocksModel();

// Remplir tous les champs obligatoires
$block->instance_id = uniqid('test_');
$block->code = 'section_test';
$block->name = 'Test Elements';
$block->zone_name = 'displayHome';
// $block->position = 0;
$block->id_shop = $id_shop;
$block->id_lang = $id_lang;
// $block->date_add = date('Y-m-d H:i:s');
// $block->date_upd = date('Y-m-d H:i:s');
$block->template = 'default'; // Template par défaut

// Configuration par défaut
$config = [
    'title' => [
        'type' => 'text',
        'label' => 'Block Title',
        'value' => 'My Test Block'
    ]
];

// JSON pour les paramètres par défaut
$defaultParams = [
    'container' => true,
    'force_full_width' => false,
    'bg_color' => '#f8f9fa',
    'paddings' => [
        'desktop' => [
            'top' => 20,
            'right' => 0,
            'bottom' => 20,
            'left' => 0,
            'unit' => 'px'
        ],
        'tablet' => [
            'top' => 15,
            'right' => 0,
            'bottom' => 15,
            'left' => 0,
            'unit' => 'px'
        ],
        'mobile' => [
            'top' => 10,
            'right' => 0,
            'bottom' => 10,
            'left' => 0,
            'unit' => 'px'
        ]
    ]
];

// Vérification et encodage des données JSON
try {
    $block->config = json_encode($config, true);
    $block->default_params = json_encode($defaultParams, true);
    $block->state = json_encode([], true);
} catch (JsonException $e) {
    die('Erreur lors de l\'encodage JSON: ' . $e->getMessage());
}

// Debug des valeurs avant sauvegarde
echo "Tentative de sauvegarde du bloc avec les valeurs suivantes:\n";
echo "Instance ID: " . $block->instance_id . "\n";
echo "Code: " . $block->code . "\n";
echo "Name: " . $block->name . "\n";
echo "Zone: " . $block->zone_name . "\n";
echo "Position: " . $block->position . "\n";
echo "ID Shop: " . $block->id_shop . "\n";
echo "ID Lang: " . $block->id_lang . "\n";
echo "Template: " . $block->template . "\n";
echo "Date Add: " . $block->date_add . "\n";
echo "Date Upd: " . $block->date_upd . "\n";

// Sauvegarde avec gestion des erreurs
if (!$block->validateFields(false)) {
    echo "Erreur de validation des champs:\n";
    print_r($block->validateFields(false));
    die();
}
$block->add();
if (!$block->id) {
    echo "Erreur lors de la sauvegarde du bloc\n";
    die();
}

echo "Bloc créé avec succès. ID: " . $block->id . PHP_EOL;
    
    // Créer un élément Section
    $elementManager = PrestaSafe\PrettyBlocks\Core\ElementManager::getInstance();
    
    // Créer une section
    $section = $elementManager->createElement('section_element', 'section_' . uniqid());
    $section->setValue('title', 'Ma section de test');
    $section->setValue('background', '#ffffff');
    $section->setValue('container', true);
    $section->setValue('paddingTop', 30);
    $section->setValue('paddingBottom', 30);
    
    // Créer deux colonnes
    $column1 = $elementManager->createElement('column_element', 'column_' . uniqid());
    $column1->setValue('width', '6');
    $column1->setValue('background', '#f5f5f5');
    $column1->setValue('padding', 20);
    
    $column2 = $elementManager->createElement('column_element', 'column_' . uniqid());
    $column2->setValue('width', '6');
    $column2->setValue('padding', 20);
    
    // Créer un paragraphe pour la première colonne
    $paragraph = $elementManager->createElement('paragraph_element', 'paragraph_' . uniqid());
    $paragraph->setValue('content', '<p>Ceci est un paragraphe de test pour démontrer le système d\'éléments de PrettyBlocks. Vous pouvez éditer ce contenu dans l\'interface d\'administration.</p>');
    $paragraph->setValue('textColor', '#333333');
    $paragraph->setValue('fontSize', 'normal');
    
    // Assembler la structure
    $column1->addElement($paragraph);
    $section->addElement($column1);
    $section->addElement($column2);
    
    // Sauvegarder les éléments pour le bloc
    $elementMapper = new PrestaSafe\PrettyBlocks\Core\Mapper\ElementMapper();
    $result = $elementMapper->saveElementsForBlock([$section], $block->id, $id_lang, $id_shop);
    
    if ($result) {
        echo "Éléments ajoutés avec succès au bloc." . PHP_EOL;
    } else {
        echo "Erreur lors de l'ajout des éléments au bloc." . PHP_EOL;
    }
