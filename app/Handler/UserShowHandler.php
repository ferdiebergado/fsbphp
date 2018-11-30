<?php

namespace App\Handler;

use App\Model\User;
use App\Command\UserShowCommand;

class UserShowHandler
{
    public function handle(UserShowCommand $command)
    {
        $id = $command->id;
        $data = cache_remember('user_' . $id, 30, (array)(User::find($id))->toArray());
        if (is_array($data)) {
            $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        return $data;
    }
}
