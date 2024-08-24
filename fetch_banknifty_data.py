import requests
import json
import os
import time
from datetime import datetime, timedelta

def fetch_and_save_banknifty_data(current_time):
    symbol = "BANKNIFTY"
    url = f"https://webapi.niftytrader.in/webapi/option/fatch-option-chain?symbol={symbol.lower()}&expiryDate="
    
    headers = {
        "Accept": "application/json, text/plain, */*",
        "Authorization": "Bearer YOUR_BEARER_TOKEN_HERE",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36",
    }

    response = requests.get(url, headers=headers)

    if response.status_code == 200:
        data = response.json()
        expiry_dates = data['resultData']['opExpiryDates']
        
        for expiry_date in expiry_dates:
            expiry_data = [item for item in data['resultData']['opDatas'] if item['expiry_date'] == expiry_date]
            formatted_expiry_date = expiry_date.replace(":", "-").split("T")[0]
            save_folder = f'banknifty/{current_time.strftime("%Y%m%d")}/{formatted_expiry_date}'
            os.makedirs(save_folder, exist_ok=True)
            save_path = os.path.join(save_folder, f'{current_time.strftime("%H%M")}.json')
            
            with open(save_path, 'w') as outfile:
                json.dump(expiry_data, outfile, indent=4)
    else:
        print(f"Failed to fetch data for {symbol} at {current_time.strftime('%H:%M')}. HTTP Status Code: {response.status_code}")

if __name__ == "__main__":
    main_start_time = datetime.now().replace(hour=9, minute=15, second=0, microsecond=0)
    main_end_time = main_start_time.replace(hour=15, minute=30)
    
    while datetime.now() < main_start_time:
        time.sleep(1)

    while datetime.now() <= main_end_time:
        current_time = datetime.now()
        fetch_and_save_banknifty_data(current_time)
        time.sleep(180)  # Wait for 3 minutes
