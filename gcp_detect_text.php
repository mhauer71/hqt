<?php
#namespace Google\Cloud\Samples\Vision;
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';
# Imports the Google Cloud client library
use Google\Cloud\Vision\VisionClient;
#$projectId = 'YOUR_PROJECT_ID';

function gcp_detect_text($projectId, $path)
{

    # Instantiates a client
    $vision = new VisionClient([
        'projectId' => $projectId,
    ]);

    # Annotate the image
#    $vision_image = $vision->image(file_get_contents($path), ['DOCUMENT_TEXT_DETECTION']);
    $vision_image = $vision->image($path, ['DOCUMENT_TEXT_DETECTION']);
    $annotation = $vision->annotate($vision_image);

    ## Print out document text
    $document = $annotation->fullText();
    #$text = $document->text();
    #printf('Document text: %s' . PHP_EOL, $text);
    return($document);
}
?>
