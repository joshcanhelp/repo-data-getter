<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class WriteStatsCsv extends WriteCsv
{

    const ELEMENTS = [
        'Repo' . SEPARATOR . 'stargazers_count',
        'PullRequests' . SEPARATOR . 'count',
        'Repo' . SEPARATOR . 'forks',
        'Issues' . SEPARATOR . 'count',
        'TrafficClones' . SEPARATOR . 'count',
        'TrafficClones' . SEPARATOR . 'uniques',
        'TrafficViews' . SEPARATOR . 'count',
        'TrafficViews' . SEPARATOR . 'uniques',
    ];

    private $data = [];

    /**
     * StatsCsv constructor.
     *
     * @param string $fileName
     *
     * @throws \Exception
     */
    public function __construct( string $fileName )
    {
        $headers = array_merge([ 'Date UTC', 'Timecode UTC' ], self::ELEMENTS);
        $elements = array_keys(self::ELEMENTS);
        $this->data = array_map(
            function () {
                return 0;
            }, $elements
        );
        parent::__construct($fileName, $headers);
    }

    /**
     * @param array $addData
     */
    public function addData( array $addData )
    {
        $this->data[$addData[0]] += $addData[1];
    }

    /**
     * @return bool
     */
    public function putClose(): bool
    {
        $row = array_values($this->data);
        $date = explode('_', DATE_NOW)[0];
        $time = explode('_', DATE_NOW)[1];
        $time = str_replace('-', ':', $time);
        $row = array_merge([ $date . SEPARATOR . $time, TIME_NOW ], $row);
        $this->putRow($row);
        return $this->close();
    }
}
