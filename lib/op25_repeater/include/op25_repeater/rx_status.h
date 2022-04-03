
#ifndef RXSTATUS_H
#define RXSTATUS_H
#include <time.h>
struct Rx_Status{
  double total_len;
  double error_count;
  double spike_count;
  time_t last_update;
  long talkgroup;
};
#endif
