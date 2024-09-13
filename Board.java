package com.example.myapplication;

import androidx.appcompat.app.AppCompatActivity;

import android.annotation.SuppressLint;
import android.os.Bundle;
import android.view.View;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.example.myapplication.databinding.ActivityBoardBinding;
import com.github.mikephil.charting.charts.LineChart;
import com.github.mikephil.charting.data.Entry;
import com.github.mikephil.charting.data.LineData;
import com.github.mikephil.charting.data.LineDataSet;

import org.json.JSONException;

import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.concurrent.atomic.AtomicReference;

public class Board extends AppCompatActivity {
    private ActivityBoardBinding binding;
    // define the strings...
    String firstLed ="0";
    String secondLed = "0";
    String thirdLed = "0";
    String forthLed = "0";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        binding = ActivityBoardBinding.inflate(getLayoutInflater());
        View view = binding.getRoot();
        super.onCreate(savedInstanceState);
        // CHART
        drawChart();

     binding.firstLED.setOnCheckedChangeListener((buttonView, isChecked) -> {
       firstLed = (isChecked)? "1":"0";
     });
     binding.secondLED.setOnCheckedChangeListener((buttonView, isChecked) -> {
        secondLed = (isChecked)? "1":"0";
     });
     binding.thirdLED.setOnCheckedChangeListener((buttonView, isChecked) -> {
        thirdLed = (isChecked)? "1" : "0";
     });
     binding.forthLED.setOnCheckedChangeListener((buttonView, isChecked) -> {
       forthLed = (isChecked)? "1" : "0";
     });

        // Communicating with Network using Volley
        binding.myfab.setOnClickListener(v -> {

            String BOARD_URL = String.format("http://rabipoor.ir/iot/nodemcu_led/update_db.php?firstLED=%s&secondLED=%s&thirdLED=%s&forthLED=%s",firstLed,secondLed,thirdLed,forthLed);
            RequestQueue requestQueue = Volley.newRequestQueue(this);
            StringRequest stringRequest = new StringRequest(Request.Method.GET, BOARD_URL, new Response.Listener<String>() {
                @Override
                public void onResponse(String response) {
                    if(response.contains("Record got ")){
                        Toast.makeText(Board.this, "LEDs got updated", Toast.LENGTH_SHORT).show();
                    }else{
                        Toast.makeText(Board.this, "a conflict during the process", Toast.LENGTH_SHORT).show();
                    }

                }
            }, error -> {
                Toast.makeText(this, "no a success in connection", Toast.LENGTH_SHORT).show();

            });
            requestQueue.add(stringRequest);

        });

        setContentView(view);
    }
    private void drawChart(){
        String SENSOR_STATE_URL = "http://rabipoor.ir/iot/nodemcu_led/android_feeds.php";
        List<Integer> getSensorIds = new ArrayList<>();
        List<Float> getSensorStates = new ArrayList<>();

        @SuppressLint("UseCompatLoadingForDrawables") JsonArrayRequest jsonArrayRequest = new JsonArrayRequest(Request.Method.GET,
                SENSOR_STATE_URL, null, response -> {
            if(response.length() > 0){
                // writing the codes....
                for(int i = 0; i < response.length(); i++){
                    try {
                        getSensorIds.add(response.getJSONObject(i) .getInt("id"));
                        getSensorStates.add(Float.parseFloat(
                                response.getJSONObject(i).getString("sensor_state")));
                    } catch (JSONException e) {
                        throw new RuntimeException(e);
                    }
                }

                //******** CHART *******//
                LineChart lineChart = new LineChart(this);
                // the id of your view
                binding.sensorChart.addView(lineChart);
                binding.sensorChart.setNoDataText("");
                List<Entry> entries = new ArrayList<>();
                for(int x = 0; x < getSensorIds.size(); x+=10){
                    entries.add(new Entry(getSensorIds.get(x), getSensorStates.get(x)));
                }
                LineDataSet dataSet = null;
                dataSet = new LineDataSet(entries, "temperature");
                dataSet.setColor(getResources().getColor(R.color.yellow));
                dataSet.setLineWidth(1.5f);
                dataSet.setMode(LineDataSet.Mode.CUBIC_BEZIER);
                dataSet.setDrawFilled(true);
                dataSet.setFillDrawable(getResources().getDrawable(R.drawable.chart_fill));
                dataSet.setDrawVerticalHighlightIndicator(false);
                dataSet.setDrawCircles(false);
                // you can add some more datasets here  lineChart.setData(new LineData(dataSet1, dataSet2));
                lineChart.setData(new LineData(dataSet));
                lineChart.invalidate();
                lineChart.animateX(5000);
                lineChart.getXAxis().setDrawGridLines(false);
                lineChart.getLineData().setValueTextColor(getResources().getColor(R.color.semixwhite));
                lineChart.getDescription().setText("");

            }else{
                Toast.makeText(this, "No record existed relevant to current time", Toast.LENGTH_SHORT).show();
            }

                }, error -> {
                Toast.makeText(this, "No connection to the destination site", Toast.LENGTH_SHORT).show();

                });

        // Request Queue
        RequestQueue requestQueue = Volley.newRequestQueue(this);
        requestQueue.add(jsonArrayRequest);

    }
}
