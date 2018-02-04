#ifndef CALL_H
#define CALL_H
#include <sys/time.h>
#include <boost/log/trivial.hpp>

struct Call_Source {
								long source;
								double position;
};

class Recorder;


#include "config.h"
#include "state.h"
#include "recorders/recorder.h"
#include "systems/parser.h"

//enum  CallState { monitoring=0, recording=1, stopping=2};

class Call {
public:

								Call( long t, double f, Config c);
								Call( TrunkMessage message, Config c);
								~Call();
								void close_call();
								void stop_call();
								bool has_stopped();
								void set_debug_recorder(Recorder *r);
								Recorder * get_debug_recorder();
								void set_recorder(Recorder *r);
								Recorder * get_recorder();
								double get_freq();
								char *get_filename();
								void create_filename();
								void set_freq(double f);
								long get_talkgroup();
								long get_source_count();
								Call_Source *get_source_list();
								void update(TrunkMessage message);
								int since_last_update();
								long stopping_elapsed();
								long elapsed();
								long get_start_time();
								void set_debug_recording(bool m);
								bool get_debug_recording();
								void set_state(State s);
								State get_state();
								void set_tdma(int m);
								int get_tdma();
								void set_encrypted(bool m);
								bool get_encrypted();
								void set_emergency(bool m);
								bool get_emergency();
private:
								State state;
								long talkgroup;
								double freq;
								time_t last_update;
								time_t start_time;
								time_t stopping_time;
								bool debug_recording;
								bool encrypted;
								bool emergency;
								char filename[160];
								int tdma;
								long source;

								Config config;
								Recorder *recorder;
								Recorder *debug_recorder;
};

#endif
