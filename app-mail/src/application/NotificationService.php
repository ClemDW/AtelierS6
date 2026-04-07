<?php

namespace photopro\mail\application;

use photopro\mail\domain\MailerInterface;


class NotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    public function handleGaleryEvent(array $data): void
    {
        $eventType = $data['event_type'] ?? '';
        $destinataire = $data['destinataire'] ?? [];
        $galery = $data['galery'] ?? [];
        $nom_galery = $galery['name'] ?? '';
        $url_galery = $galery['url'] ?? '';
        $email = $destinataire['email'] ?? null;
        $nom = ($destinataire['prenom'] ?? '') . ' ' . ($destinataire['nom'] ?? '');

        switch ($eventType) {
            case 'galery.modification':
                $this->sendGaleryModifEmail($email, $nom, $nom_galery, $url_galery);
                break;
            case 'galery.publication':
                $this->sendGaleryPublicationEmail($email, $nom, $nom_galery, $url_galery);
                break;
            default:
                echo "[NOTIFICATION] Type d'événement inconnu: {$eventType}\n";
        }

    }

    private function sendGaleryModifEmail(string $to, string $toName, string $galery_url, string $galery_name): void
    {

        $subject = "Modification de galerie - {$galery_name}";

        $htmlBody = $this->buildGaleryModifEmail($to, $toName, $galery_name, $galery_url);

        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        if ($this->mailer->send($to, $subject, $htmlBody, $textBody)) {
            echo "[EMAIL ENVOYÉ] Modification galerie → {$to}\n";
        }
    }

    private function buildGaleryModifEmail(string $to, string $toName, string $galery_name, string $galery_url): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
            </head>
            <body>
                <h2>Galerie {$galery_name} modifiée</h2>
                <p>Bonjour {$toName},</p>
                <p>Votre galerie a été modifiée, vous pouvez y accéder via cette url : <br>{$galery_url}</p>
                <p>PhotoPro</p>
            </body>
            </html>
        HTML;
    }

    private function sendGaleryPublicationEmail(string $to, string $toName, string $galery_url, string $galery_name): void
    {

        $subject = "Publication de galerie - {$galery_name}";

        $htmlBody = $this->buildGaleryPublicationEmail($to, $toName, $galery_name, $galery_url);

        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        if ($this->mailer->send($to, $subject, $htmlBody, $textBody)) {
            echo "[EMAIL ENVOYÉ] Publication galerie → {$to}\n";
        }
    }

    private function buildGaleryPublicationEmail(string $to, string $toName, string $galery_name, string $galery_url): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
            </head>
            <body>
                <h2>Galerie {$galery_name} publiée</h2>
                <p>Bonjour {$toName},</p>
                <p>Votre galerie a été publiée, vous pouvez y accéder via cette url : <br>{$galery_url}</p>
                <p>PhotoPro</p>
            </body>
            </html>
        HTML;
    }
}
