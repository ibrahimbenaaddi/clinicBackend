<?php

namespace App\Services;

use App\Http\Resources\availableSlotResource;
use App\Models\AppointmentSlot;
use App\Traits\Searchable;
use App\Traits\ServiceResponse;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentSlotService
{
    use ServiceResponse, Searchable;

    private static array $validStatus = [
        'available',
        'full',
        'blocked',
        'cancelled',
    ];

    public function getAllSlots(Request $request)
    {
        try {
            $query = AppointmentSlot::query()->with('doctor.user');
            $query = self::whereQuery($query, $request, 'status', self::$validStatus);
            if ($request->filled('search')) {
                $term = '%' . $request->query('search') . '%';
                $query->where(function ($q) use ($term) {
                    $q->WhereHas('doctor.user', function ($uq) use ($term) {
                        $uq->where('firstname', 'like', $term)
                            ->orWhere('lastname',  'like', $term);
                    });
                });
            }
            $query = $this->sortByDate($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllAppointments', 'AppointmentService', $e);
        }
    }

    public function createSlot(array $credentials)
    {
        try {
            DB::beginTransaction();
            $slot = AppointmentSlot::create($credentials);

            if (blank($slot)) {
                DB::rollBack();
                return self::theLog('createSlot', 'AppointmentSlotService', new Exception('The slot is not created'));
            }

            $slot->load(['doctor.user']);
            DB::commit();
            return $slot;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('createSlot', 'AppointmentSlotService', $e);
        }
    }

    public function getSlot(int $slotId)
    {
        try {
            return AppointmentSlot::with('doctor.user')->findOrFail($slotId);
        } catch (Exception $e) {
            return self::theLog('getSlot', 'AppointmentSlotService', $e);
        }
    }

    public function updateSlot(array $credentials, int $slotId)
    {
        try {
            DB::beginTransaction();

            $slot = AppointmentSlot::with('doctor')->findOrFail($slotId);

            $isUpdated = $slot->update($credentials);
            if (!$isUpdated) {
                DB::rollBack();
                return self::theLog('updateSlot', 'AppointmentSlotService', new Exception('The slot is not updated'));
            }

            if (array_key_exists('start_time', $credentials) && array_key_exists('end_time', $credentials)) {
                $isUpdated = $slot->appointments()->update([
                    'start_time' => $credentials['start_time'],
                    'end_time' => $credentials['end_time']
                ]);

                if (!$isUpdated) {
                    DB::rollBack();
                    return self::theLog('updateSlot', 'AppointmentSlotService', new Exception('The appointments of this slot : ' . $slot->slot_id . ' is not updated'));
                }
            }

            $slot->refresh();
            $slot->load('doctor.user');

            DB::commit();
            return $slot;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('updateSlot', 'AppointmentSlotService', $e);
        }
    }

    public function deleteSlot(int $slotId)
    {
        try {
            DB::beginTransaction();

            $slot = AppointmentSlot::with(['doctor'])->findOrFail($slotId);

            $isDeleted = $slot->appointments()->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteSlot', 'AppointmentSlotService', new Exception('The appointments of this slot : ' . $slot->slot_id . ' is not deleted'));
            }

            $isDeleted = $slot->delete();
            if (!$isDeleted) {
                DB::rollBack();
                return self::theLog('deleteSlot', 'AppointmentSlotService', new Exception('The slot is not deleted'));
            }


            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return self::theLog('deleteSlot', 'AppointmentSlotService', $e);
        }
    }

    public function availableByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = AppointmentSlot::query()->with('doctor.user')
                ->where('doctor_id', $doctorId)
                ->where('status', 'available')
                ->orderBy('start_time');

            $uniqueSlots = $this->sortByDate($query, $request)->get()->unique(function ($slot) {
                return $slot->start_time->format('Y-m-d H:i:s');
            });
            $grouped = $uniqueSlots->groupBy(fn($slot) => $slot->start_time->toDateString())
                ->map(fn($day) => availableSlotResource::collection($day));
            return $grouped;
        } catch (Exception $e) {
            return self::theLog('availableByDoctor', 'AppointmentSlotService', $e);
        }
    }

    public function getAllByDoctor(Request $request, int $doctorId)
    {
        try {
            $query = AppointmentSlot::query()->with('doctor.user')
                ->where('doctor_id', $doctorId);
            $query = $this->sortByDate($query, $request);
            self::limitThePages($query, $request);
            return $query->latest()->paginate(self::$perPage);
        } catch (Exception $e) {
            return self::theLog('getAllByDoctor', 'AppointmentSlotService', $e);
        }
    }

    private function sortByDate(Builder $query, Request $request): Builder
    {
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->query('date'));
        } elseif ($request->filled('from') || $request->filled('to')) {
            $query->when($request->filled('from'), fn($q) => $q->whereDate('start_time', '>=', $request->query('from')))
                ->when($request->filled('to'), fn($q) => $q->whereDate('start_time', '<=', $request->query('to')));
        }
        return $query;
    }
}
