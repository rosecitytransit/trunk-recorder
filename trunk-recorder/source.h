#ifndef SOURCE_H
#define SOURCE_H
#include <iostream>
#include <numeric>
#include <gnuradio/basic_block.h>
#include <gnuradio/top_block.h>
#include <osmosdr/source.h>
////#include <gnuradio/uhd/usrp_source.h>
#include "config.h"
//#include "recorders/recorder.h"
#include "recorders/analog_recorder.h"
#include "recorders/debug_recorder.h"
#include "recorders/p25_recorder.h"

class Source
{
								static int rec_num;
								double min_hz;
								double max_hz;
								double center;
								double rate;
								double actual_rate;
								double error;
								double ppm;
								double squelch_db;
								double fsk_gain;
								double analog_levels;
								double digital_levels;
								int gain;
								int bb_gain;
								int if_gain;
								int lna_gain;
								int mix_gain;
								int max_digital_recorders;
								int max_debug_recorders;
								int max_analog_recorders;
								bool qpsk_mod;
								int silence_frames;
								Config * config;

								std::vector<p25_recorder_sptr> digital_recorders;
								std::vector<debug_recorder_sptr> debug_recorders;
								std::vector<analog_recorder_sptr> analog_recorders;
								std::string driver;
								std::string device;
								std::string antenna;
								gr::basic_block_sptr source_block;

public:
								int get_num_available_recorders();
								Source(double c, double r, double e, std::string driver, std::string device, Config *cfg);
								gr::basic_block_sptr get_src_block();
								double get_min_hz();
								double get_max_hz();
								double get_center();
								double get_rate();
								std::string get_driver();
								std::string get_device();
								void set_antenna(std::string ant);
								std::string get_antenna();
								void set_error(double e);
								double get_error();
								void set_if_gain(int i);
								int get_if_gain();
								void set_squelch_db(double s);
								double get_squelch_db();
								void set_gain(int r);
								int get_gain();
								void set_fsk_gain(double r);
								double get_fsk_gain();
								void set_qpsk_mod(bool m);
								bool get_qpsk_mod();
								void set_silence_frames(int m);
								int get_silence_frames();
								void set_analog_levels(double r);
								double get_analog_levels();
								void set_digital_levels(double r);
								double get_digital_levels();
								void set_bb_gain(int b);
								int get_bb_gain();
								void set_mix_gain(int b);
								int get_mix_gain();
								void set_lna_gain(int b);
								int get_lna_gain();
								void set_freq_corr(double p);
								void print_recorders();
								void tune_digital_recorders();
								Config * get_config();
								analog_recorder_sptr create_conventional_recorder(gr::top_block_sptr tb);
								p25_recorder_sptr create_conventionalP25_recorder(gr::top_block_sptr tb);
								void create_analog_recorders(gr::top_block_sptr tb, int r);
								Recorder * get_analog_recorder(int priority);
								void create_digital_recorders(gr::top_block_sptr tb, int r);
								Recorder * get_digital_recorder(int priority);
								void create_debug_recorders(gr::top_block_sptr tb, int r);
								Recorder * get_debug_recorder();
								inline osmosdr::source::sptr cast_to_osmo_sptr(gr::basic_block_sptr p)
								{
																return boost::dynamic_pointer_cast<osmosdr::source, gr::basic_block>(p);
								}

};
#endif
