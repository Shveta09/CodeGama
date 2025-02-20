<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function addFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->get();
        $wallet = $wallet[0];

        if (!$wallet->checkDailyLimit($request->amount)) {
            return response()->json(['message' => 'Daily transaction limit exceeded'], 404);
        }

        if ($wallet->detectSuspiciousActivity($request->amount)) {
            return response()->json(['message' => 'Fraudulent activity detected'], 404);
        }

        $wallet->balance += $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'description' => 'Deposit funds',
        ]);

        return response()->json(['message' => 'Funds added successfully to account', 'balance' => $wallet->balance], 200);
    }

    public function setLimit(Request $request)
    {
        $request->validate([
            'limit' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->get();
        $wallet = $wallet[0];
        $wallet->limit = $request->limit;
        $wallet->save();

        return response()->json(['message' => 'Daily limit set', 'balance' => $wallet->limit], 200);
    }

    public function checkBalance(Request $request)
    {
        
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->get();

       

        return response()->json(['balance' => $wallet[0]->balance], 200);
    }

    public function withdrawFunds(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->get();
        $wallet = $wallet[0];
        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient funds'], 404);
        }

        
        if (!$wallet->checkDailyLimit($request->amount)) {
            return response()->json(['message' => 'Daily transaction limit exceeded'], 404);
        }

        if ($wallet->detectSuspiciousActivity($request->amount)) {
            return response()->json(['message' => 'Fraudulent activity detected'], 404);
        }

        $wallet->balance -= $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'description' => 'Withdraw funds',
        ]);

        return response()->json(['message' => 'Funds withdrawn successfully', 'balance' => $wallet->balance], 200);
    }

    // Transfer funds between wallets
    public function transferFunds(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $sender = Auth::user();
        $recipient = User::findOrFail($request->recipient_id);

        $wallet = Wallet::where('user_id', $sender->id)->get();
        $senderWallet = $wallet[0];
        $wallet = Wallet::where('user_id', $recipient->id)->get();
        $recipientWallet = $wallet[0];

        if ($senderWallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient funds'], 400);
        }
        

        $senderWallet->balance -= $request->amount;
        $recipientWallet->balance += $request->amount;

        $senderWallet->save();
        $recipientWallet->save();

        Transaction::create([
            'wallet_id' => $senderWallet->id,
            'type' => 'transfer_out',
            'amount' => $request->amount,
            'description' => 'Transferred to user ' . $recipient->name,
        ]);

        // Log recipient transaction
        Transaction::create([
            'wallet_id' => $recipientWallet->id,
            'type' => 'transfer_in',
            'amount' => $request->amount,
            'description' => 'Received from user ' . $sender->name,
        ]);

        return response()->json(['message' => 'Funds transferred successfully', 'sender_balance' => $senderWallet->balance], 200);
    }

    public function transactionHistory()
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->get();
        $wallet = $wallet[0];

        // Get the transaction history for the user's wallet
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }
}
