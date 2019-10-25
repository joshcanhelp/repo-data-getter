<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class ReadStatsCsv extends ReadCsv
{

    /**
     * @param string $type
     *
     * @return array
     */
    public function getPreviousStats( string $type ): array
    {
        $csvArray = $this->getCsv();

        // Get the CSV headers as keys for the returned array.
        $formattedStats = array_flip($csvArray[0]);

        // If we only have a single row, then there is no data to pull from.
        // Return 0 for all values.
        if (1 === count($csvArray) ) {
            return array_map(
                function () {
                    return 0; 
                }, $formattedStats
            );
        }

        $lastStats = $csvArray[ count($csvArray) - 1 ];
        $returnStats = [];
        foreach ( $formattedStats as $statName => $index ) {
            $statPrefix = $type . SEPARATOR;
            if (0 !== strpos($statName, $statPrefix) ) {
                continue;
            }
            $returnStats[ str_replace($statPrefix, '', $statName) ] = $lastStats[$index];
        }
        return $returnStats;
    }
}
