<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class MyClient extends Model
{
    protected $table = 'my_client';

    protected $fillable = [
        'name', 'slug', 'is_project', 'self_capture', 'client_prefix', 
        'client_logo', 'address', 'phone_number', 'city', 'created_at', 'updated_at', 'deleted_at'
    ];

    public $timestamps = false;

    // Method untuk menyimpan data ke Redis
    public static function storeInRedis($client)
    {
        // Menyimpan data ke Redis dengan key 'slug' dan isi data dalam format JSON
        $key = 'client:' . $client->slug;
        Cache::put($key, $client->toJson(), now()->addDays(30));  // Simpan selama 30 hari
    }

    // Method untuk menghapus data dari Redis saat data diupdate atau dihapus
    public static function deleteFromRedis($slug)
    {
        $key = 'client:' . $slug;
        Cache::forget($key);
    }

    // Menyimpan atau memperbarui data dan mengupdate Redis
    public static function createOrUpdateClient($validatedData, $logoFile = null)
    {
        if ($logoFile) {
            // Upload file logo ke S3 dan simpan URL-nya
            $logoPath = $logoFile->store('client_logos', 's3');
            $validatedData['client_logo'] = Storage::disk('s3')->url($logoPath);
        }

        // Update or Create client berdasarkan slug
        $client = self::updateOrCreate(
            ['slug' => $validatedData['slug']],
            $validatedData
        );

        // Hapus cache Redis yang lama (jika ada)
        self::deleteFromRedis($client->slug);

        // Simpan cache Redis dengan data yang baru
        self::storeInRedis($client);

        return $client;
    }

    // Soft delete dan hapus dari Redis
    public static function softDeleteClient($id)
    {
        // Cari client berdasarkan ID
        $client = self::find($id);

        if ($client) {
            // Update kolom deleted_at untuk menandai data sebagai soft deleted
            $client->update(['deleted_at' => now()]);

            // Menghapus cache Redis yang terkait dengan client
            self::deleteFromRedis($client->slug);

            return $client;
        }

        return null;
    }
}
