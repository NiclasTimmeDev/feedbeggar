<?php

namespace App\Http\Controllers;

use App\Helpers\ExceptionHelper;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class SubscriptionController extends Controller
{

    /**
     * The constructor method.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function makeUserPremium()
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            return ExceptionHelper::customSingleError('You are not logged in.', 401);
        }

        // Get the user data from stripe.
        $stripe_user = $user->createOrGetStripeCustomer();

        /*
         * We must now check that the user actually has active
         * subscriptions. This means, all subscriptions of the user must
         * have the statzs 'active'.
         * There might for some reason be more than one subscription.
         * However, the app only supports one subsctiption plan, so
         * multiple subscriptions should actually not happen.
         */
        $subscriptions = $stripe_user->subscriptions->data;

        // If we don't have any subscriptions.
        if (empty($subscriptions)) {
            return $user->update([
                'is_premium' => 0
            ]);
        }

        // Check that all subscriptions are active.
        $all_active = TRUE;
        foreach ($subscriptions as $subscription) {
            $status = $subscription->status;

            if ($status !== 'active') {
                $all_active = FALSE;
                break;
            }
        }

        // Make the user non-premium if not all are active.
        if (!$all_active) {
            return $user->update([
                'is_premium' => 0
            ]);
        }

        // If we got this far, make the user premium.
        $user->update([
            'is_premium' => 1
        ]);

    }

    /**
     * Create a new sessionID that can be used
     * to redirect to the Stripe checkout page.
     *
     * @param Request $request
     * @return array|JsonResponse
     */
    public function subscriptionIntention(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user instanceof User) {
                return ExceptionHelper::customSingleError('You are not logged in.', 401);
            }

            $checkout = $user->newSubscription('default', env('STRIPE_PRODUCT_ID', ''))
                ->checkout([
                    'success_url' => route('subscription-success'),
                    'cancel_url' => route('subscription-error'),
                ]);

            return [
                'session' => $checkout
            ];
        } catch (Throwable $e) {
            return ExceptionHelper::customSingleError('Sorry, something went wrong. Please try again later.', 500);
        }
    }
}
