USE [Allocate7]
GO

SET ANSI_NULLS ON
GO


SET QUOTED_IDENTIFIER ON
GO


IF EXISTS (SELECT 1 FROM LeaveAllocateTypes WHERE AllocName='Additional')
BEGIN
  
  update LeaveAllocateTypes set isLeaveCounted=NULL where AllocName='Additional';

END

IF EXISTS (SELECT 1 FROM LeaveAllocateTypes WHERE AllocName='Under11TOIL')
BEGIN
  
  update LeaveAllocateTypes set isLeaveCounted=NULL where AllocName='Under11TOIL';

END

IF EXISTS (SELECT 1 FROM LeaveAllocateTypes WHERE AllocName='Over12TOIL')
BEGIN
  
  update LeaveAllocateTypes set isLeaveCounted=NULL where AllocName='Over12TOIL';

END


IF EXISTS (SELECT 1 FROM LeaveAllocateTypes WHERE AllocName='LongService')
BEGIN
  
  update LeaveAllocateTypes set isLeaveCounted=NULL where AllocName='LongService';

END

GO