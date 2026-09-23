<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'message', 'read_at'])]
class ContactMessage extends Model
{
	/**
	 * @return array<string, string>
	 */
	protected function casts(): array
	{
		return [
			'read_at' => 'datetime',
		];
	}
}
