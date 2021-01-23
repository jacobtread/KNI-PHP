<?php

namespace Jacobtread\KNI;

use Exception;

class Notices {

    private string $date;
    private ?array $notices = null;
    private ?string $errorMessage = null;
    private ?Exception $errorCause = null;

    /**
     * Notices constructor.
     * @param string $date The date these notices are for
     */
    public function __construct(string $date) {
        $this->date = $date;
    }

    /**
     * @param array|null $notices
     */
    public function setNotices(?array $notices): void {
        $this->notices = $notices;
    }

    /**
     * Get whether or not the request succeeded
     *
     * @return bool Whether or not the request was a success
     */
    public function isSuccess(): bool {
        return $this->errorMessage === null && $this->notices !== null;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return Exception|null
     */
    public function getErrorCause(): ?Exception {
        return $this->errorCause;
    }

    /**
     * @param Exception|null $errorCause
     */
    public function setErrorCause(?Exception $errorCause): void {
        $this->errorCause = $errorCause;
    }

    /**
     * @return string
     */
    public function getDate(): string {
        return $this->date;
    }

    /**
     * @param ?callable $filter
     * @return array
     */
    public function getNotices(?callable $filter = null): array {
        if ($filter !== null) {
            return array_filter($this->notices, $filter);
        }
        return $this->notices;
    }

    /**
     * @return array
     */
    public function getMeetings(): array {
        return array_filter($this->notices, function (Notice $notice) {
            return $notice->isMeeting();
        });
    }

}