<?php namespace LogExpander\Events;

class FacetofaceAttendenceTaken extends FacetofaceEvent {
    /**
     * Reads data for an event.
     * @param [String => Mixed] $opts
     * @return [String => Mixed]
     * @override Event
     */
    public function read(array $opts) {
        $other = unserialize($opts['other']);
        //$session = $this->repo->readFacetofaceSession($other['sessionid']);
        $objectid = $opts['objectid'];
        $opts['objectid'] = $other['sessionid'];
        return array_merge(parent::read($opts), [
            'signups' => $this->repo->readFacetofaceSessionSignup($objectid),
            //'session' => $session
        ]);
    }
}