USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_add_PHLLeaveAllocation]    Script Date: 08/07/2022 14:41:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_add_PHLLeaveAllocation]

@staffnumber VARCHAR(255),
@iyear INT,
@phlleaveamt VARCHAR(255),
@holidayname VARCHAR(MAX),
@teamid INT,
@holidayid INT,
@scheduledpersonid INT,
@userid INT,
@historytype INT,
@currentuser VARCHAR(30),
@status	INT OUTPUT

AS
BEGIN
	SET NOCOUNT ON;
	BEGIN TRY
		DECLARE @hoildydate VARCHAR(30), @newid INT,@HistoryStart nvarchar(100)
		Select @hoildydate = dDateTime from TimeDimension where ID=@holidayid
		
		INSERT INTO LeaveAllocation(StaffNumber,iYear,Annual,PHL,TOIL,Comp,Additional,Exceptional,dDate,Comments,WebCredit,Under11TOIL,Over12TOIL,SchedulingTeamid,Casual,TimeDemensionID,SchedulingPersonID,is_PHL,CreatedDate,CreatedBy,UpdateDate,UpdatedBy) VALUES(@staffnumber,@iyear,NULL,@phlleaveamt,NULL,NULL,NULL,NULL,CONVERT(DATETIME,@hoildydate, 102),@holidayname,1,'0.00','0.00',@teamid,NULL,@holidayid,@scheduledpersonid,1,
		CONVERT(DATETIME,GETUTCDATE(),102),@userid,CONVERT(DATETIME,GETUTCDATE(),102),@userid)
		SET @newid = Scope_Identity();
		SET @HistoryStart  = 'New entry created by ' +  @currentuser + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '.<br>'
		
		--insert history
		INSERT INTO [dbo].[History]([HistoryType],[UserID],[History],[datetime],[AttributeID])
		VALUES (@historytype ,@userid,@HistoryStart,GETDATE(),@newid)
		SET @status = 1;
		return @status;
	END TRY
	BEGIN CATCH
		INSERT INTO ErrorLog VALUES(ERROR_NUMBER(),ERROR_STATE(),ERROR_SEVERITY(),ERROR_LINE(),'usp_add_PHLLeaveAllocation',ERROR_MESSAGE(),GETDATE(),@userid)
		SET @status = 0;
		return @status;
	END CATCH
END