<?php

namespace App\Library;

use Google\Cloud\Dlp\V2\CharacterMaskConfig;
use Google\Cloud\Dlp\V2\Client\DlpServiceClient;
use Google\Cloud\Dlp\V2\ContentItem;
use Google\Cloud\Dlp\V2\InfoType;
use Google\Cloud\Dlp\V2\InspectConfig;
use Google\Cloud\Dlp\V2\InspectContentRequest;
use Google\Cloud\Dlp\V2\Likelihood;
use Google\Cloud\Dlp\V2\DeidentifyConfig;
use Google\Cloud\Dlp\V2\DeidentifyContentRequest;
use Google\Cloud\Dlp\V2\InfoTypeTransformations;
use Google\Cloud\Dlp\V2\InfoTypeTransformations\InfoTypeTransformation;
use Google\Cloud\Dlp\V2\PrimitiveTransformation;


class DLPService
{
    /**
     * Inspects the given content for sensitive information using the Cloud Data Loss Prevention (DLP) service.
     *
     * @param string $projectID The ID of the Google Cloud project.
     * @param string $content The content to be inspected.
     * @return array An array of findings, each containing the excerpt, info type, likelihood, and probability.
     */
    static function inspect(string $projectID, string $content): array
    {
        $dlp = new DlpServiceClient();
        $parent = "projects/$projectID/locations/global";
        $item = (new ContentItem())->setValue($content);

        $inspectConfig = (new InspectConfig())
            ->setInfoTypes(DLPService::buildInfoTypes())
            ->setIncludeQuote(true);

        # Send request
        $inspectContentRequest = (new InspectContentRequest())
            ->setParent($parent)
            ->setInspectConfig($inspectConfig)
            ->setItem($item);
        $response = $dlp->inspectContent($inspectContentRequest);

        // Print the results
        $findings = [];
        $res = $response->getResult()->getFindings();

        foreach ($res as $finding) {
            $findings[] = [
                'excerpt' => $finding->getQuote(),
                'infoType' => $finding->getInfoType()->getName(),
                'likelihood' => Likelihood::name($finding->getLikelihood()),
                'probability' => $finding->getLikelihood()
            ];
        }

        return $findings;
    }

    /**
     * Builds an array of InfoType objects based on the application configuration settings.
     *
     * @return array An array of InfoType objects.
     */
    private static function buildInfoTypes()
    {
        $infoTypes = [];
        collect(config('settings.dlp.info_types'))->each(function ($type) use (&$infoTypes) {
            $infoTypes[] = (new InfoType())->setName($type);
        });

        return $infoTypes;
    }

    /**
     * Redacts the given content by masking sensitive information using the Cloud Data Loss Prevention (DLP) service.
     *
     * @param string $projectID The ID of the Google Cloud project.
     * @param string $content The content to be redacted.
     * @return string The redacted content.
     */
    public static function redact(string $projectID, string $content): string
    {
        # Instantiate a client.
        $dlp = new DlpServiceClient();
        $parent = "projects/$projectID/locations/global";

        $inspectConfig = (new InspectConfig())->setInfoTypes(DLPService::buildInfoTypes());

        # Masking configuration
        $maskConfig = (new CharacterMaskConfig())->setMaskingCharacter('#');

        # Transformation configuration
        $primitiveTransformation = (new PrimitiveTransformation())
            ->setCharacterMaskConfig($maskConfig);

        $infoTypeTransformation = (new InfoTypeTransformation())
            ->setPrimitiveTransformation($primitiveTransformation)
            ->setInfoTypes(DLPService::buildInfoTypes());

        $infoTypeTransformations = (new InfoTypeTransformations())
            ->setTransformations([$infoTypeTransformation]);

        # Deidentification configuration
        $deidentifyConfig = (new DeidentifyConfig())
            ->setInfoTypeTransformations($infoTypeTransformations);

        # Run request
        $item = (new ContentItem())->setValue($content);
        $deidentifyContentRequest = (new DeidentifyContentRequest())
            ->setParent($parent)
            ->setInspectConfig($inspectConfig)
            ->setDeidentifyConfig($deidentifyConfig)
            ->setItem($item);

        $response = $dlp->deidentifyContent($deidentifyContentRequest);

        return $response->getItem()->getValue();
    }
}
