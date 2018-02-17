(
  coord_lon       double,               -- coord.lon
  coord_lat       double,               -- coord.lat
  main_temp       double,               -- main.temp
  main_pressure   double,               -- main.pressure
  main_humidity   double,               -- main.humidity
  main_temp_min   double,               -- main.temp_min
  main_temp_max   double,               -- main.temp_max
  main_sea_level  double,               -- main.sea_level
  main_grnd_level double,               -- main.grnd_level
  wind_speed      double,               -- wind.speed
  wind_deg        double,               -- wind.deg
  clouds_all      double,               -- clouds.all
  rain_3h         double,               -- rain.3h
  snow_3h         double,               -- snow.3h
  timestamp       datetime primary key, -- dt
  city_id         bigint,               -- id
  city_name       varchar(256)          -- name
)