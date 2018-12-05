<?php

namespace Bergado\Presentation\Web\Pub\Controller\Handler;

use Bergado\Domain\Entity\User;
use Bergado\Infrastructure\Cache\Cache;
use Bergado\Presentation\Web\Pub\Controller\Command\UserShowCommand;

class UserShowHandler extends Handler
{
    public function handle(UserShowCommand $command)
    {
        $id = $command->id;
        $key = 'user_' . $id;

        $data = $this->cache->recall($key, 30, function () {
            return User::find($id)->toArray();
        });

        if (is_array($data)) {
            $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        return $data;
    }
}