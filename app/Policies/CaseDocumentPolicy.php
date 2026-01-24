<?php

namespace App\Policies;

use App\Models\CaseDocument;
use App\Models\User;

class CaseDocumentPolicy
{
    public function upload(User $user): bool
    {
        return $user->hasPermissionTo('case.document.upload');
    }

    public function delete(User $user, CaseDocument $document): bool
    {
        return $user->hasPermissionTo('case.document.delete');
    }

    public function view(User $user, CaseDocument $document): bool
    {
        if (($document->is_public ?? false) || ($document->case && ($document->case->is_public ?? false))) {
            return true;
        }

        return $user->hasPermissionTo('case.document.view');
    }
}
