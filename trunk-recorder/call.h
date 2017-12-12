#ifndef CALL_H
#define CALL_H
#include <sys/time.h>
#include <boost/log/trivial.hpp>

struct Call_Source {
								long source;
								long time;
								double position;

};

struct Call_Freq {
	double freq;
	long time;
	double position;
	double total_len;
	double error_count;
	double spike_count;
};

struct Call_Error {
	double freq;
	double sample_count;
	double error_count;
	double spike_count;
};

class Recorder;
#include "../op25_repeater/include/op25_repeater/rx_status.h"
#include "uploaders/call_uploader.h"
#include "config.h"
#include "state.h"
#include "recorders/recorder.h"
#include "systems/system.h"
#include "systems/parser.h"


class System;
//enum  CallState { monitoring=0, recording=1, stopping=2};

class Call {
public:

								Call( long t, double f, System *s, Config c);
								Call( TrunkMessage message, System *s, Config c);
								~Call();
								void restart_call();
								void end_call();
								void set_debug_recorder(Recorder *r);
								Recorder * get_debug_recorder();
								void set_recorder(Recorder *r);
								Recorder * get_recorder();
								double get_freq();

								char *get_converted_filename();
								char *get_filename();
                                                                char *get_filetime();
								int get_sys_num();
								std::string get_short_name();
								void create_filename();
								void set_error(Rx_Status rx_status);
								void set_freq(double f);
								long get_talkgroup();
								long get_source_count();
								Call_Source *get_source_list();
								Call_Freq *get_freq_list();
								Call_Error *get_error_list();
								long get_error_list_count();
								long get_freq_count();
								void update(TrunkMessage message);
								int get_idle_count();
								void increase_idle_count();
								void reset_idle_count();
								int since_last_update();
								long stopping_elapsed();
								long elapsed();
								long get_start_time();
								double get_current_length();
								long get_stop_time();
								void set_debug_recording(bool m);
								bool get_debug_recording();
								void set_state(State s);
								State get_state();
								void set_phase2_tdma(bool m);
								bool get_phase2_tdma();
								void set_tdma_slot(int s);
								int get_tdma_slot();
								const char * get_xor_mask();
								bool is_conventional();
								void set_conventional(bool conv);
								void set_encrypted(bool m);
								bool get_encrypted();
								void set_emergency(bool m);
								bool get_emergency();
private:
								State state;
								long talkgroup;
								double curr_freq;
								System *sys;
								std::string short_name;
								int src_count;
								long curr_src_id;
								Call_Error error_list[50];
								Call_Source src_list[50];
								Call_Freq freq_list[50];
								long error_list_count;
								long freq_count;
								time_t last_update;
								int idle_count;
								time_t stop_time;
								time_t start_time;
								bool debug_recording;
								bool encrypted;
								bool emergency;
								bool conventional;
								char filename[255];
                                                                char filetime[7];
								char converted_filename[255];
								char status_filename[255];
								bool phase2_tdma;
								int tdma_slot;

								Config config;
								Recorder *recorder;
								Recorder *debug_recorder;
								bool add_source(long src);

};

#endif
