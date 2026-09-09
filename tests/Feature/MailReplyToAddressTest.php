<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Mail\LeadNotificationMail;
use App\Mail\RecruitmentFormMail;
use App\Models\Lead;
use Tests\TestCase;

class MailReplyToAddressTest extends TestCase
{
    public function test_recruitment_form_uses_the_sender_email_as_reply_to_address(): void
    {
        $envelope = (new RecruitmentFormMail(
            senderName: 'Samuel Olbrich',
            senderEmail: 'samuel@example.test',
            teamName: 'Muži C',
            messageBody: 'Mám zájem hrát.',
            subjectText: 'Nová náborová zpráva',
        ))->envelope();

        $this->assertSame('samuel@example.test', $envelope->replyTo[0]->address);
        $this->assertSame('Samuel Olbrich', $envelope->replyTo[0]->name);
    }

    public function test_contact_form_uses_the_sender_email_as_reply_to_address(): void
    {
        $envelope = (new ContactFormMail(
            senderName: 'Samuel Olbrich',
            senderEmail: 'samuel@example.test',
            recipientEmail: 'club@example.test',
            messageBody: 'Dobrý den.',
            subjectText: 'Kontakt',
        ))->envelope();

        $this->assertSame('samuel@example.test', $envelope->replyTo[0]->address);
        $this->assertSame('Samuel Olbrich', $envelope->replyTo[0]->name);
    }

    public function test_lead_notification_uses_the_lead_email_as_reply_to_address(): void
    {
        $lead = Lead::factory()->make([
            'name' => 'Samuel Olbrich',
            'email' => 'samuel@example.test',
        ]);

        $envelope = (new LeadNotificationMail($lead))->envelope();

        $this->assertSame('samuel@example.test', $envelope->replyTo[0]->address);
        $this->assertSame('Samuel Olbrich', $envelope->replyTo[0]->name);
    }
}
