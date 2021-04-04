<?php

include __DIR__ . '/vendor/autoload.php';

use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\Channel\Message;

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


$commandTrigger = '!';


$discord = new Discord([
    'token' => $_ENV['DISCORD_API_KEY'],
]);

$discord->on('ready', function ($discord) {

    echo "Bot is ready!", PHP_EOL;

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        $reply = "Command Executed";
        // Check if a command was sent
        if (substr($message->content, 0, 1) === '!') {
            $reply = checkCommands($message);
            $message->reply($reply);
        }

        echo "{$message->author->username}: {$message->content}", PHP_EOL;
    });
});


function checkCommands($message)
{
    $contentArray = explode(' ', $message->content);

    $command = strtolower(substr($contentArray[0], 1));

    $allowedCommands = [
        'title',
        'help',
        'cht',
        'whois',
    ];

    $reply = "Sorry, not sure what you are asking for \r\n";
    $reply .= commandHelp();

    if (in_array($command, $allowedCommands)) {
        switch ($command) {
            case 'help':
                $reply = commandHelp();
                break;
            case 'title':
                $reply = commandTitle($message);
                break;
            case 'cht':
                $reply = commandCht($message);
                break;
            case 'whois':
                $reply = commandWhois($message);
                break;
            default:
                $reply .= commandHelp();
        }
    }

    return $reply;
}

function commandTitle($message)
{
    $title = explode(' ', $message->content, 2);
    $content = $title[1] . " (@{$message->author->username}) \r\n";
    $file = file_put_contents(
        $_ENV['TITLES_FILE'],
        $content,
        FILE_APPEND | LOCK_EX
    );

    return "I added your title suggestion, {$title[1]}";
}

function commandHelp()
{
    $reply = "\r\n!help - This help list. \r\n";
    $reply .= "!title {Title Suggestion} - Suggest a title for the show. \r\n";
    $reply .= "!cht {PHP Function} - Look up something with cheat.sh \r\n";
    $reply .= "!whois {PHPUgly Host Name} - A good place to start your stalking of PHPUgly host \r\n";
    return $reply;
}

function commandCht($message)
{
    $title = explode(' ', $message->content, 2);

    // This isn't working yet. I think I need to break
    // this response down to smaller chunks
    //
    // $reply = shell_exec('~/.local/bin/cht.sh php ' . $title[1]);
    // var_dump($reply);

    // For now we will just return the link to the website.
    return 'https://cht.sh/php/' . $title[1];
}

function commandWhois($message)
{
    $who = explode(' ', $message->content, 2);
    $reply = 'Not sure which one that is. Host names are Eric, John, and Tom';
    $name = strtolower($who[1]);

    if (in_array($name, ['tom', 'thomas', 'pfy'])) {
        $reply = 'https://twitter.com/realrideout';
    }

    if (in_array($name, ['john', 'jon', 'johncongdon'])) {
        $reply = 'https://twitter.com/johncongdon';
    }

    if (in_array($name, ['eric', 'erick', 'erik', 'shocm'])) {
        $reply = 'https://twitter.com/shocm';
    }

    return $reply;
}

$discord->run();
