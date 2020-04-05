<?php

namespace App;

use App\Entity\Comment;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    /**
     * 2 : если комментарий является явным спамом;
     * 1 : если комментарий может быть спамом;
     * 0 : если комментарий не спам (так называемый ham)
     */
    const COMMENT_SPAM     = 2;
    const COMMENT_CAN_SPAM = 1;
    const COMMENT_HAM      = 0;

    private $client;
    private $endpoint;

    public function __construct(HttpClientInterface $client, string $akismetKey)
    {
        $this->client = $client;
        $this->endpoint = sprintf(
            'https://%s.rest.akismet.com/1.1/comment-check',
            $akismetKey
        );
    }

    /**
     * @param  Comment  $comment
     * @param  array    $context
     *
     * @return int Spam score: 0: not spam, 1: maybe spam, 2: blatant spam
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->client->request(
            'POST',
            $this->endpoint,
            [
                'body' => array_merge(
                    $context,
                    [
                        'blog'                 => 'http://guestbook.local',
                        'comment_type'         => 'comment',
                        'comment_author'       => $comment->getAuthor(),
                        'comment_author_email' => $comment->getEmail(),
                        'comment_content'      => $comment->getText(),
                        'comment_date_gmt'     => $comment->getCreatedAt()->format('c'),
                        'blog_lang'            => 'en',
                        'blog_charset'         => 'UTF-8',
                        'is_test'              => true,
                    ]
                ),
            ]
        );

        $headers = $response->getHeaders();

        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return self::COMMENT_SPAM;
        }

        $content = $response->getContent();

        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to check for spam: %s(%s).',
                    $content,
                    $headers['x-akismet-debug-help'][0]
                )
            );
        }

        return 'true' === $content ? self::COMMENT_CAN_SPAM : self::COMMENT_HAM;
    }
}