@props(['event', 'showActions' => true])

<livewire:member.event-card :event="$event" :showActions="$showActions" :key="$event['type'].'-'.$event['data']->id" />
