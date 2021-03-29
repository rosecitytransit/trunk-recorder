#ifndef P25_RECORDER_DECODE_H
#define P25_RECORDER_DECODE_H

#include <boost/shared_ptr.hpp>
#include <gnuradio/block.h>
#include <gnuradio/block.h>
#include <gnuradio/hier_block2.h>
#include <gnuradio/io_signature.h>
#include <gnuradio/msg_queue.h>
#include <gnuradio/blocks/short_to_float.h>

#include <op25_repeater/gardner_costas_cc.h>
#include <op25_repeater/fsk4_slicer_fb.h>
#include <op25_repeater/include/op25_repeater/fsk4_demod_ff.h>
#include <op25_repeater/include/op25_repeater/p25_frame_assembler.h>
#include <op25_repeater/include/op25_repeater/rx_status.h>
#include <op25_repeater/vocoder.h>

#if GNURADIO_VERSION < 0x030800
#include <gnuradio/blocks/multiply_const_ff.h>
#else
#include <gnuradio/blocks/multiply_const.h>
#endif

#include <gr_blocks/nonstop_wavfile_sink.h>
#include <gr_blocks/nonstop_wavfile_sink_impl.h>

class p25_recorder_decode;
typedef boost::shared_ptr<p25_recorder_decode> p25_recorder_decode_sptr;
p25_recorder_decode_sptr make_p25_recorder_decode(  int silence_frames);

class p25_recorder_decode : public gr::hier_block2 {
  friend p25_recorder_decode_sptr make_p25_recorder_decode( int silence_frames);

protected:

  virtual void initialize(  int silence_frames);
  gr::op25_repeater::p25_frame_assembler::sptr op25_frame_assembler;
    gr::msg_queue::sptr traffic_queue;
  gr::msg_queue::sptr rx_queue;
  gr::op25_repeater::fsk4_slicer_fb::sptr slicer;
    gr::blocks::short_to_float::sptr converter;
      gr::blocks::multiply_const_ff::sptr levels;
   gr::blocks::nonstop_wavfile_sink::sptr wav_sink;
public:
  p25_recorder_decode();
  void set_tdma_slot(int slot);
  void set_xor_mask(const char *mask);
  void switch_tdma(bool phase2_tdma); 
  void start(Call *call);
  void reset_rx_status();
  Rx_Status get_rx_status();
  bool get_call_terminated();
  void stop();
  int tdma_slot;
  bool delay_open;
  virtual ~p25_recorder_decode();
  double get_current_length();
};
#endif
