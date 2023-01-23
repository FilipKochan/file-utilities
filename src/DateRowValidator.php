<?php

namespace FilipKochan\FileUtilities;

use PhpOffice\PhpSpreadsheet as P;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class DateRowValidator implements RowValidator
{
    private string $date_column;
    /**
     * @param string $date_column this column will be checked for valid date
     */
    public function __construct(string $date_column) {
        $this->date_column = $date_column;
    }
    public function is_valid(Row $row): bool {
        $prev = P\Calculation\Functions::getReturnDateType();
        P\Calculation\Functions::setReturnDateType(P\Calculation\Functions::RETURNDATE_PHP_OBJECT);
        try {
            foreach ($row->getCellIterator() as $cell) {
                try {
                    if ($cell->getColumn() !== $this->date_column) {
                        continue;
                    }

                    $v = $cell->getCalculatedValue();
//                    echo $v;

                    if (is_numeric($v)) {
                        $UNIX_DATE = (int)(($v - 25569) * 86400);
                        \DateTime::createFromFormat("U", $UNIX_DATE, new \DateTimeZone("Europe/Prague"));
                        return true;
                    }

                    $dates = explode("-", $v);

                    foreach ($dates as $date) {
                        $date = preg_replace("/\s/", "", $date);
                        if (
                            (\DateTime::createFromFormat('j/n/Y', $date, new \DateTimeZone("Europe/Prague"))) ||
                            (\DateTime::createFromFormat('j.n.Y', $date, new \DateTimeZone("Europe/Prague"))) ||
                            (\DateTime::createFromFormat('j/n', $date, new \DateTimeZone("Europe/Prague"))) ||
                            (\DateTime::createFromFormat('j.n.', $date, new \DateTimeZone("Europe/Prague")))
                        ) {/* ok */} else { return false; }
                    }
                } catch (\Exception) {
                    return false;
                }
            }

            return true;
        } finally {
            P\Calculation\Functions::setReturnDateType($prev);
        }
    }

    private function date_formats(): string {
        return '<ul>
                    <li><code>dd. mm.</code></li>
                    <li><code>dd. mm. - dd. mm.</code></li>
                    <li><code>dd. mm. rrrr</code></li>
                    <li><code>dd. mm. rrrr - dd. mm. rrrr</code></li>
                </ul>';
    }
    public function get_rules(): string
    {
        return '<p>Datum musí být v jednom z formátů' . $this->date_formats() . '</p>';
    }

    public function get_error_help(): string
    {
        return '<p>Opravte datum tak, aby odpovídalo jednomu z formátů ' . $this->date_formats() . '</p>';
    }
}