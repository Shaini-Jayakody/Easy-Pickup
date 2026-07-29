<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\User;
use App\Traits\BookingTrait;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase as BaseTestCase;

class TestBookingTraitClass
{
    use BookingTrait;
}

class BookingTraitTest extends BaseTestCase
{
    public function testRejectsRentalsShorterThanTwoHours(): void
    {
        $trait = new TestBookingTraitClass();

        $start = Carbon::now()->addDay()->setHour(10)->setMinute(0)->setSecond(0);
        $end = $start->copy()->addHours(1);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Rental duration must be at least 2 hours.');

        $trait->validateRentalDuration($start->toDateTimeString(), $end->toDateTimeString());
    }

    public function testRejectsRentalsLongerThanOneMonth(): void
    {
        $trait = new TestBookingTraitClass();

        $start = Carbon::now()->addWeek()->setHour(10)->setMinute(0)->setSecond(0);
        $end = $start->copy()->addDays(31);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Rental duration cannot exceed 1 month.');

        $trait->validateRentalDuration($start->toDateTimeString(), $end->toDateTimeString());
    }

    public function testTreatsCapitalizedPendingStatusAsPending(): void
    {
        $booking = new Booking();
        $booking->status = 'Pending';

        $this->assertTrue($booking->isPending());
    }

    public function testAllowsCancellationWhenAuthenticatedUserMatchesBookingOwnerAcrossStringAndIntegerIds(): void
    {
        $trait = new TestBookingTraitClass();

        $booking = new Booking();
        $booking->user_id = '1';
        $booking->status = 'pending';
        $booking->rental_start_date = Carbon::now()->addDay();
        $booking->rental_end_date = Carbon::now()->addDay()->addHours(2);

        $user = new User();
        $user->user_id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('secret123');

        $this->actingAs($user);

        $this->assertTrue($trait->canCancelBooking($booking));
    }
}
