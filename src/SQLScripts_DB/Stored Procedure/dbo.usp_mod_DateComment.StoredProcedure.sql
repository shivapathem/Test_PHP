USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_mod_DateComment]    Script Date: 03/09/2025 16:54:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER       PROCEDURE [dbo].[usp_mod_DateComment]
    @CommentId INT,
    @Date DATE,
    @TeamId INT,
    @Comment NVARCHAR(500),
	@History TEXT,
    @UserId INT
AS
BEGIN
    -- Check if the record exists
    IF EXISTS (
        SELECT 1
        FROM DateComment
        WHERE CommentDate = @Date AND TeamId = @TeamId
    )
    BEGIN
        -- Update existing record
        UPDATE DateComment
        SET Comment = @Comment, History = @History, LastModDate = GETDATE(), LastModBy = @UserId
        WHERE CommentDate = @Date AND TeamId = @TeamId AND ID = @CommentId;
    END
    ELSE
    BEGIN
        -- Insert new record
        INSERT INTO DateComment (CommentDate, TeamId, Comment, History, CreatedDate, CreatedBy)
        VALUES (@Date, @TeamId, @Comment, @History, GETDATE(), @UserId);
    END
	IF NULLIF(@Comment, '') IS NULL
	BEGIN

        -- Delete the record if @Comment is empty
        DELETE FROM DateComment
        WHERE CommentDate = @Date AND TeamId = @TeamId AND ID = @CommentId;;
     END
END;