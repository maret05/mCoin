
<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\User;

class McoinController
{
    const baseURL = 'http://104.131.162.155/order/';

    /**
     * Logs a message.
     *
     * @param string $level The level of the log ('info', 'error', etc.).
     * @param string $message The log message.
     * @param array $context Additional context for the log.
     */
    public static function log($level, $message, $context = [])
    {
        // file_put_contents('log.txt', "$level: $message - " . json_encode($context) . PHP_EOL, FILE_APPEND);
    }

    /**
     * Create a transaction.
     *
     * @param string $mcoinAmount The amount for the transaction.
     * @param string $userId The user ID.
     * @param string $orderNumber The order number.
     * @param string $note Additional notes.
     * @return array Result of the transaction.
     * @throws Exception If an error occurs during the HTTP request.
     */
    public static function createTransaction($mcoinAmount, $userId, $orderNumber, $note = '')
    {
        // Prepare payload
        $mcoinAmount = self::aMCoin($mcoinAmount);
        $formattedAmount = sprintf('%.0f', $mcoinAmount);

        $payload = [
            'amount' => strval($formattedAmount)."",
            'customer' => $userId,
            'item' => $orderNumber,
            'note' => $note
        ];

        error_log(json_encode($payload).PHP_EOL, 3, 'mcointran.log');

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::baseURL . 'create');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        // Execute the POST request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            self::log('error', 'cURL error in createTransaction', ['error' => curl_error($ch)]);
            throw new \Exception(curl_error($ch));
        }

        // Decode the response
        $decodedResponse = json_decode($response, true);

        // Close the cURL session
        curl_close($ch);

        self::log('info', 'createTransaction response', ['response' => $decodedResponse]);
        return $decodedResponse;
    }

    /**
     * Get the status of a transaction.
     *
     * @param string $mcoinTx The transaction ID.
     * @return array|null The status of the transaction or null if not found.
     * @throws Exception If an error occurs during the HTTP request.
     */
    public static function getStatus($mcoinTx)
    {
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::baseURL . 'get/' . $mcoinTx);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the GET request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            self::log('error', 'cURL error in getStatus', ['error' => curl_error($ch)]);
            throw new \Exception(curl_error($ch));
        }

        // Decode the response
        $decodedResponse = json_decode($response, true);

        // Close the cURL session
        curl_close($ch);

        self::log('info', 'getStatus response', ['response' => $decodedResponse]);
        return $decodedResponse;
    }

    /**
     * Convert amount to aMCoin format.
     *
     * @param string $amount The amount to convert.
     * @return string The converted amount.
     */
    static function aMCoin(float $mCoins): string
    {
        //return $mCoins * 1000000000000000000;
        return bcmul($mCoins, '1000000000000000000');
    }

    public function orders()
    {
        $json = file_get_contents('orders.json');

        return json_decode($json, true);
    }

    public function clearOrder(User $admin, string $orderID, string $adminPassword)
    {
        try {
            $url = self::baseURL . "/admin/clear-order";
            $response = Http::asJson()->acceptJson()->post($url,
                [
                    'orderId' => $orderID,
                    'password' => $adminPassword,
                    'newStatus' => 'cleared'
                ]
            );
            $response->throw();
            $arResponse = $response->json();
            if (empty($arResponse)) {
                return null;
            }
            return $arResponse;

        } catch (\RequestException $e) {
            Log::error($e->getMessage(), ['server_error' => $e->response->body(), 'server_status' => $e->response->status()]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        throw new \Exception("Unable to clear mcoin transaction status for OrderID: $orderID");

    }

    public function getStatusByTx(string $tx)
    {
        try {
            $url = self::baseURL . "/order/address/$tx";
            $response = Http::asJson()->acceptJson()->get($url);
            $response->throw();
            $arResponse = $response->json();
            if (empty($arResponse)) {
                return null;
            }
            return $arResponse;

        } catch (\RequestException $e) {
            Log::error($e->getMessage(), ['server_error' => $e->response->body(), 'server_status' => $e->response->status()]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
        throw new \Exception("Unable to get mcoin transaction status for tX: $tx");
    }
}
