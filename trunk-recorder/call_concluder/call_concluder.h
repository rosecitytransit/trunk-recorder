#ifndef CALL_CONCLUDER_H
#define CALL_CONCLUDER_H
#include <queue>
#include <list>
#include <vector>
#include <thread>
#include <future>
#include <boost/regex.hpp>
#include <ctime>

#include "../call.h"
#include "../global_structs.h"
#include "../formatter.h"
#include "../systems/system.h"
/*
class Uploader;
#include "../uploaders/uploader.h"
#include "../uploaders/call_uploader.h"
#include "../uploaders/broadcastify_uploader.h"
#include "../uploaders/openmhz_uploader.h"*/


enum Call_Data_Status { INITIAL, SUCCESS, RETRY, FAILED };
struct Call_Data_t {
  long talkgroup;
  long source;
  bool duplex;
  bool mode;
  int priority;
  std::vector<unsigned long> patched_talkgroups;
  std::string talkgroup_tag;
  std::string talkgroup_alpha_tag;
  std::string talkgroup_description;
  std::string talkgroup_group;
  long call_num;
  double freq;
  long start_time;
  long stop_time;
  bool encrypted;
  bool emergency;
  bool audio_archive;
  bool transmission_archive;
  bool call_log;
  bool compress_wav;
  char filename[300];
  char status_filename[300];
  char converted[300];
  double error_count;
  double spike_count;



  std::string short_name;
  std::string upload_script;
  std::string audio_type;

  int tdma_slot;
  double length;
  bool phase2_tdma;

  std::vector<Call_Source> transmission_source_list;
  std::vector<Transmission> transmission_list;

  Call_Data_Status status;
  time_t process_call_time;
  int retry_attempt;
};

Call_Data_t upload_call_worker(Call_Data_t call_info);

class Call_Concluder {
static const int MAX_RETRY = 2;
public:
static std::list<Call_Data_t> retry_call_list;
static std::list<std::future<Call_Data_t>> call_data_workers;

static Call_Data_t create_call_data(Call *call, System *sys, Config config);

static void conclude_call(Call *call, System *sys, Config config);

static void manage_call_data_workers();

};




#endif
