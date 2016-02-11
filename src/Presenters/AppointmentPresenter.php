<?php

namespace Timegridio\Concierge\Presenters;

use McCool\LaravelAutoPresenter\BasePresenter;
use Timegridio\Concierge\Models\Appointment;

class AppointmentPresenter extends BasePresenter
{
    public function __construct(Appointment $resource)
    {
        $this->wrappedObject = $resource;
    }

    public function code()
    {
        $length = $this->wrappedObject->business->pref('appointment_code_length');

        return strtoupper(substr($this->wrappedObject->hash, 0, $length));
    }

    public function date($format = 'Y-m-d')
    {
        if ($this->wrappedObject->start_at->isToday()) {
            return studly_case(trans('Concierge::appointments.text.today'));
        }

        if ($this->wrappedObject->start_at->isTomorrow()) {
            return studly_case(trans('Concierge::appointments.text.tomorrow'));
        }

        return $this->wrappedObject
                    ->start_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format($format);
    }

    public function arriveAt()
    {
        if (!$this->wrappedObject->business->pref('appointment_flexible_arrival')) {
            return $this->time;
        }

        $fromTime = $this->wrappedObject
                         ->vacancy
                         ->start_at
                         ->timezone($this->wrappedObject->business->timezone)
                         ->format(config('root.time.format'));

        $toTime = $this->wrappedObject
                       ->vacancy
                       ->finish_at
                       ->timezone($this->wrappedObject->business->timezone)
                       ->format(config('root.time.format'));

        return ucwords(trans('Concierge::appointments.text.from_to', ['from' => $fromTime, 'to' => $toTime]));
    }

    public function time()
    {
        return $this->wrappedObject
                    ->start_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format(config('root.time.format'));
    }

    public function finishTime()
    {
        return $this->wrappedObject
                    ->finish_at
                    ->timezone($this->wrappedObject->business->timezone)
                    ->format(config('root.time.format'));
    }

    public function diffForHumans()
    {
        return $this->wrappedObject->start_at->timezone($this->wrappedObject->business->timezone)->diffForHumans();
    }

    public function phone()
    {
        return $this->wrappedObject->business->phone;
    }

    public function location()
    {
        return $this->wrappedObject->business->postal_address;
    }

    public function statusLetter()
    {
        return substr(trans('Concierge::appointments.status.'.$this->wrappedObject->statusLabel), 0, 1);
    }

    public function status()
    {
        return trans('Concierge::appointments.status.'.$this->wrappedObject->statusLabel);
    }

    public function statusIcon()
    {
        return '<span class="label label-'.$this->statusToCssClass().'">'.$this->statusLetter.'</span>';
    }

    public function statusToCssClass()
    {
        switch ($this->wrappedObject->status) {
            case Appointment::STATUS_ANNULATED:
                return 'danger';
                break;
            case Appointment::STATUS_CONFIRMED:
                return 'success';
                break;
            case Appointment::STATUS_RESERVED:
                return 'warning';
                break;
            case Appointment::STATUS_SERVED:
            default:
                return 'default';
        }
    }

    public function panel()
    {
        return view('widgets.appointment.panel._body', ['appointment' => $this, 'user' => auth()->user()])->render();
    }

    public function row()
    {
        return view('widgets.appointment.row._body', ['appointment' => $this, 'user' => auth()->user()])->render();
    }
}
